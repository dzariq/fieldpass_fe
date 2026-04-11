-- MySQL 8+: Matches + logos + match_events JSON + possession.
--
-- Possession % is BY TIME each team is credited with the ball (match_posession segments):
--   home_possession_pct = ROUND(100 * home_possession_seconds / home_away_tracked_total_seconds, 1)
--   away_possession_pct = ROUND(100 * away_possession_seconds / home_away_tracked_total_seconds, 1)
-- Denominator = home + away tracked seconds only (neutral / ball-out segments excluded from %).
--
-- Two modes (App\Models\MatchPosession::summarizeForMatch):
--   playing_clock     — every row has playing_elapsed_seconds: segments use playing clock + LEAD.
--   legacy_wall_clock — any NULL playing_elapsed_seconds: segments use event_at wall time,
--                       last segment ends COALESCE(timer_pause_started_at, NOW()).
--
-- n8n: Data Table + Webhook node names below; adjust if yours differ.

SELECT
    m.id,
    m.home_club_id,
    m.away_club_id,
    m.home_score,
    m.away_score,
    m.date,
    m.matchweek,
    m.competition_id,
    m.status,

    home_club.name AS home_club_name,
    CASE
        WHEN home_club.logo_url IS NOT NULL THEN home_club.logo_url
        ELSE CONCAT('{{ $('Data Table').item.json.fieldpass_url }}/', home_club.avatar)
    END AS home_club_logo,

    away_club.name AS away_club_name,
    CASE
        WHEN away_club.logo_url IS NOT NULL THEN away_club.logo_url
        ELSE CONCAT('{{ $('Data Table').item.json.fieldpass_url }}/', away_club.avatar)
    END AS away_club_logo,

    (
        SELECT COALESCE(
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    'event_id', me.event_id,
                    'event_type', me.event_type,
                    'minute', me.minute_in_match,
                    'club_id', me.club_id,
                    'player_id', me.player_id
                )
            ),
            JSON_ARRAY()
        )
        FROM (
            SELECT
                me.event_id,
                me.event_type,
                me.minute_in_match,
                me.club_id,
                me.player_id
            FROM match_events me
            WHERE me.match_id = m.id
            ORDER BY me.minute_in_match ASC, me.event_id ASC
        ) AS me
    ) AS match_actions_json,

    pos.home_possession_pct,
    pos.away_possession_pct,
    pos.home_possession_seconds,
    pos.away_possession_seconds,
    pos.home_away_tracked_total_seconds,
    pos.neutral_possession_seconds,
    pos.before_first_switch_seconds,

    CONCAT(
        FLOOR(COALESCE(pos.home_possession_seconds, 0) / 60),
        ':',
        LPAD(CAST(MOD(COALESCE(pos.home_possession_seconds, 0), 60) AS CHAR), 2, '0')
    ) AS home_possession_mmss,

    CONCAT(
        FLOOR(COALESCE(pos.away_possession_seconds, 0) / 60),
        ':',
        LPAD(CAST(MOD(COALESCE(pos.away_possession_seconds, 0), 60) AS CHAR), 2, '0')
    ) AS away_possession_mmss,

    CONCAT(
        FLOOR(COALESCE(pos.neutral_possession_seconds, 0) / 60),
        ':',
        LPAD(CAST(MOD(COALESCE(pos.neutral_possession_seconds, 0), 60) AS CHAR), 2, '0')
    ) AS neutral_possession_mmss,

    CONCAT(
        FLOOR(COALESCE(pos.before_first_switch_seconds, 0) / 60),
        ':',
        LPAD(CAST(MOD(COALESCE(pos.before_first_switch_seconds, 0), 60) AS CHAR), 2, '0')
    ) AS before_first_switch_mmss,

    pos.possession_pct_computed

FROM `match` m
INNER JOIN club AS home_club ON m.home_club_id = home_club.id
INNER JOIN club AS away_club ON m.away_club_id = away_club.id

LEFT JOIN (
    SELECT
        mx.id AS match_id,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            WHEN pl.match_id IS NOT NULL AND pl.total_tracked > 0 THEN ROUND(100 * pl.home_sec / pl.total_tracked, 1)
            WHEN lg.match_id IS NOT NULL AND lg.total_tracked > 0 THEN ROUND(100 * lg.home_sec / lg.total_tracked, 1)
        END AS home_possession_pct,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            WHEN pl.match_id IS NOT NULL AND pl.total_tracked > 0 THEN ROUND(100 * pl.away_sec / pl.total_tracked, 1)
            WHEN lg.match_id IS NOT NULL AND lg.total_tracked > 0 THEN ROUND(100 * lg.away_sec / lg.total_tracked, 1)
        END AS away_possession_pct,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            ELSE COALESCE(pl.home_sec, lg.home_sec)
        END AS home_possession_seconds,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            ELSE COALESCE(pl.away_sec, lg.away_sec)
        END AS away_possession_seconds,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            ELSE COALESCE(pl.total_tracked, lg.total_tracked)
        END AS home_away_tracked_total_seconds,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            ELSE COALESCE(pl.neutral_sec, lg.neutral_sec)
        END AS neutral_possession_seconds,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN NULL
            ELSE COALESCE(pl.before_first_switch_seconds, lg.before_first_switch_seconds)
        END AS before_first_switch_seconds,

        CASE
            WHEN COALESCE(pc.poss_row_count, 0) = 0 THEN 'no_possession_rows'
            WHEN pl.match_id IS NOT NULL THEN 'playing_clock'
            WHEN lg.match_id IS NOT NULL THEN 'legacy_wall_clock'
            ELSE 'no_possession_rows'
        END AS possession_pct_computed

    FROM `match` mx
    LEFT JOIN (
        SELECT
            match_id,
            COUNT(*) AS poss_row_count
        FROM match_posession
        GROUP BY match_id
    ) pc ON pc.match_id = mx.id

    LEFT JOIN (
        SELECT
            agg.match_id,
            agg.home_sec,
            agg.away_sec,
            agg.neutral_sec,
            agg.total_tracked,
            (
                SELECT mp0.playing_elapsed_seconds
                FROM match_posession mp0
                WHERE mp0.match_id = agg.match_id
                ORDER BY mp0.event_at ASC, mp0.id ASC
                LIMIT 1
            ) AS before_first_switch_seconds
        FROM (
            SELECT
                p_seg.match_id,
                SUM(CASE WHEN p_seg.club_id = mh.home_club_id THEN p_seg.seg_sec ELSE 0 END) AS home_sec,
                SUM(CASE WHEN p_seg.club_id = mh.away_club_id THEN p_seg.seg_sec ELSE 0 END) AS away_sec,
                SUM(CASE WHEN p_seg.club_id IS NULL THEN p_seg.seg_sec ELSE 0 END) AS neutral_sec,
                SUM(CASE WHEN p_seg.club_id IN (mh.home_club_id, mh.away_club_id) THEN p_seg.seg_sec ELSE 0 END) AS total_tracked
            FROM (
                SELECT
                    mp.match_id,
                    mp.club_id,
                    GREATEST(
                        0,
                        CAST(COALESCE(
                            LEAD(mp.playing_elapsed_seconds) OVER (
                                PARTITION BY mp.match_id
                                ORDER BY mp.event_at, mp.id
                            ),
                            cp.current_playing_seconds
                        ) AS SIGNED)
                        - CAST(mp.playing_elapsed_seconds AS SIGNED)
                    ) AS seg_sec
                FROM match_posession mp
                INNER JOIN (
                    SELECT
                        id AS match_id,
                        CASE
                            WHEN started_at IS NULL THEN NULL
                            ELSE GREATEST(
                                0,
                                CAST(TIMESTAMPDIFF(SECOND, started_at, NOW(6)) AS SIGNED)
                                - CAST(COALESCE(timer_paused_seconds, 0) AS SIGNED)
                                - CAST(
                                    CASE
                                        WHEN timer_pause_started_at IS NOT NULL
                                        THEN TIMESTAMPDIFF(SECOND, timer_pause_started_at, NOW(6))
                                        ELSE 0
                                    END AS SIGNED
                                )
                            )
                        END AS current_playing_seconds
                    FROM `match`
                ) cp ON cp.match_id = mp.match_id
                INNER JOIN (
                    SELECT match_id
                    FROM match_posession
                    GROUP BY match_id
                    HAVING SUM(CASE WHEN playing_elapsed_seconds IS NULL THEN 1 ELSE 0 END) = 0
                ) all_have_playing ON all_have_playing.match_id = mp.match_id
                WHERE mp.playing_elapsed_seconds IS NOT NULL
            ) AS p_seg
            INNER JOIN `match` mh ON mh.id = p_seg.match_id
            GROUP BY p_seg.match_id, mh.home_club_id, mh.away_club_id
        ) AS agg
    ) pl ON pl.match_id = mx.id

    LEFT JOIN (
        SELECT
            agg.match_id,
            agg.home_sec,
            agg.away_sec,
            agg.neutral_sec,
            agg.total_tracked,
            bf.before_first_switch_seconds
        FROM (
            SELECT
                p_seg.match_id,
                SUM(CASE WHEN p_seg.club_id = mh.home_club_id THEN p_seg.seg_sec ELSE 0 END) AS home_sec,
                SUM(CASE WHEN p_seg.club_id = mh.away_club_id THEN p_seg.seg_sec ELSE 0 END) AS away_sec,
                SUM(CASE WHEN p_seg.club_id IS NULL THEN p_seg.seg_sec ELSE 0 END) AS neutral_sec,
                SUM(CASE WHEN p_seg.club_id IN (mh.home_club_id, mh.away_club_id) THEN p_seg.seg_sec ELSE 0 END) AS total_tracked
            FROM (
                SELECT
                    mp.match_id,
                    mp.club_id,
                    GREATEST(
                        0,
                        TIMESTAMPDIFF(
                            SECOND,
                            mp.event_at,
                            COALESCE(
                                LEAD(mp.event_at) OVER (
                                    PARTITION BY mp.match_id
                                    ORDER BY mp.event_at, mp.id
                                ),
                                COALESCE(mx.timer_pause_started_at, NOW(6))
                            )
                        )
                    ) AS seg_sec
                FROM match_posession mp
                INNER JOIN `match` mx ON mx.id = mp.match_id
                INNER JOIN (
                    SELECT match_id
                    FROM match_posession
                    GROUP BY match_id
                    HAVING SUM(CASE WHEN playing_elapsed_seconds IS NULL THEN 1 ELSE 0 END) > 0
                ) use_legacy ON use_legacy.match_id = mp.match_id
            ) AS p_seg
            INNER JOIN `match` mh ON mh.id = p_seg.match_id
            GROUP BY p_seg.match_id, mh.home_club_id, mh.away_club_id
        ) AS agg
        LEFT JOIN (
            SELECT
                mx2.id AS match_id,
                CASE
                    WHEN mx2.started_at IS NULL THEN NULL
                    WHEN fe.first_event_at IS NULL THEN NULL
                    WHEN fe.first_event_at <= mx2.started_at THEN 0
                    ELSE GREATEST(
                        0,
                        TIMESTAMPDIFF(
                            SECOND,
                            mx2.started_at,
                            LEAST(fe.first_event_at, COALESCE(mx2.timer_pause_started_at, NOW(6)))
                        )
                    )
                END AS before_first_switch_seconds
            FROM `match` mx2
            LEFT JOIN (
                SELECT
                    match_id,
                    MIN(event_at) AS first_event_at
                FROM match_posession
                GROUP BY match_id
            ) fe ON fe.match_id = mx2.id
        ) bf ON bf.match_id = agg.match_id
    ) lg ON lg.match_id = mx.id

) AS pos ON pos.match_id = m.id

WHERE m.competition_id = {{ $('Webhook').item.json.query.competition_id }}

ORDER BY m.matchweek ASC, m.date ASC;
