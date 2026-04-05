<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Fetches a profile image per player: tries English Wikipedia pageimage (Wikimedia CDN),
 * then falls back to ui-avatars.com (initials). Saves under public/avatars like PlayersController.
 *
 * Wikipedia media may be CC-BY-SA or similar — ensure your product complies if you use those images.
 */
class FetchCompetitionPlayerAvatars extends Command
{
    protected $signature = 'players:fetch-competition-avatars
                            {competition_id=7 : Competition ID (e.g. 7)}
                            {--force : Overwrite players that already have an avatar path set}
                            {--dry-run : List targets only; no HTTP, files, or DB updates}
                            {--limit= : Max players to process (default: all)}
                            {--wiki-only : Only try Wikipedia; do not use initials fallback}
                            {--generated-only : Skip Wikipedia; only generate initials avatars}';

    protected $description = 'For players in a competition (via ACTIVE competition_club + player_club), fetch Wikimedia thumbnail or generate avatar PNG into public/avatars and update players.avatar';

    private const USER_AGENT = 'FieldpassFe/1.0 (competition avatar helper; +https://fieldpass.com.my)';

    public function handle(): int
    {
        $competitionId = (int) $this->argument('competition_id');
        $force = (bool) $this->option('force');
        $wikiOnly = (bool) $this->option('wiki-only');
        $generatedOnly = (bool) $this->option('generated-only');

        if ($wikiOnly && $generatedOnly) {
            $this->error('Use only one of --wiki-only or --generated-only.');

            return self::FAILURE;
        }

        $playerIds = DB::table('players')
            ->join('player_club', 'players.id', '=', 'player_club.player_id')
            ->join('competition_club', 'player_club.club_id', '=', 'competition_club.club_id')
            ->where('competition_club.competition_id', $competitionId)
            ->where('competition_club.status', 'ACTIVE')
            ->distinct()
            ->orderBy('players.id')
            ->pluck('players.id');

        if ($playerIds->isEmpty()) {
            $this->warn("No players found for competition_id={$competitionId} (ACTIVE competition_club + player_club).");

            return self::SUCCESS;
        }

        $limitOpt = $this->option('limit');
        if ($limitOpt !== null && $limitOpt !== '') {
            $playerIds = $playerIds->take(max(1, (int) $limitOpt));
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run — no network or disk writes. Players that would be considered: '.$playerIds->count());
            foreach ($playerIds as $pid) {
                $player = Player::query()->find($pid);
                if (! $player) {
                    continue;
                }
                $has = $this->playerHasUsableAvatar($player);
                $action = ($has && ! $force) ? 'skip (has file)' : ($force || ! $has ? 'would fetch' : 'skip');
                $this->line("#{$player->id}\t{$player->name}\t{$action}");
            }

            return self::SUCCESS;
        }

        $avatarsDir = public_path('avatars');
        if (! is_dir($avatarsDir)) {
            if (! @mkdir($avatarsDir, 0755, true) && ! is_dir($avatarsDir)) {
                $this->error("Cannot create directory: {$avatarsDir}");

                return self::FAILURE;
            }
        }

        $this->info('Players to process: '.$playerIds->count());

        $ok = 0;
        $skip = 0;
        $fail = 0;

        foreach ($playerIds as $pid) {
            $player = Player::query()->find($pid);
            if (! $player) {
                continue;
            }

            $hasAvatar = $this->playerHasUsableAvatar($player);
            if ($hasAvatar && ! $force) {
                $this->line("Skip #{$player->id} {$player->name} (already has avatar)");
                $skip++;

                continue;
            }

            if ($hasAvatar && $force && $player->avatar) {
                $this->deleteOldPublicAvatar($player->avatar);
            }

            $binary = null;
            $ext = 'png';
            $source = 'none';

            if (! $generatedOnly) {
                $thumbUrl = $this->wikipediaThumbnailUrl($player->name);
                if ($thumbUrl) {
                    $downloaded = $this->downloadImageIfAllowed($thumbUrl);
                    if ($downloaded !== null) {
                        [$binary, $ext] = $downloaded;
                        $source = 'wikipedia';
                    }
                }
            }

            if ($binary === null && ! $wikiOnly) {
                $fallbackUrl = $this->uiAvatarsUrl($player->name);
                $downloaded = $this->downloadImageIfAllowed($fallbackUrl);
                if ($downloaded !== null) {
                    [$binary, $ext] = $downloaded;
                    $source = 'ui-avatars';
                }
            }

            if ($binary === null) {
                $this->error("FAIL #{$player->id} {$player->name} — could not obtain image");
                $fail++;

                continue;
            }

            $filename = 'player_'.$player->id.'_'.Str::random(8).'.'.$ext;
            $relative = 'avatars/'.$filename;

            if (file_put_contents(public_path($relative), $binary) === false) {
                $this->error("FAIL #{$player->id} write {$relative}");
                $fail++;

                continue;
            }

            $player->avatar = $relative;
            $player->saveQuietly();
            $this->info("OK #{$player->id} {$player->name} ← {$source} → {$relative}");
            $ok++;

            usleep(350_000);
        }

        $this->newLine();
        $this->table(['Result', 'Count'], [
            ['OK', $ok],
            ['Skipped', $skip],
            ['Failed', $fail],
        ]);

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function playerHasUsableAvatar(Player $player): bool
    {
        $path = trim((string) ($player->avatar ?? ''));
        if ($path === '' || $path === '0') {
            return false;
        }

        $full = public_path($path);

        return is_file($full) && filesize($full) > 0;
    }

    private function deleteOldPublicAvatar(string $relative): void
    {
        $relative = ltrim($relative, '/');
        $full = public_path($relative);
        if (str_starts_with($full, public_path()) && is_file($full)) {
            @unlink($full);
        }
    }

    private function wikipediaThumbnailUrl(string $playerName): ?string
    {
        $q = trim($playerName);
        if ($q === '') {
            return null;
        }

        try {
            $search = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(20)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $q.' footballer',
                    'format' => 'json',
                    'srlimit' => '1',
                    'srnamespace' => '0',
                ]);

            if (! $search->successful()) {
                return null;
            }

            $titles = data_get($search->json(), 'query.search.0.title');
            if (! is_string($titles) || $titles === '') {
                return null;
            }

            $img = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(20)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'titles' => $titles,
                    'prop' => 'pageimages',
                    'format' => 'json',
                    'pithumbsize' => '400',
                ]);

            if (! $img->successful()) {
                return null;
            }

            $pages = data_get($img->json(), 'query.pages', []);
            if (! is_array($pages)) {
                return null;
            }

            $first = reset($pages);
            $url = is_array($first) ? ($first['thumbnail']['source'] ?? null) : null;

            return is_string($url) && $url !== '' ? $url : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function uiAvatarsUrl(string $name): string
    {
        $enc = rawurlencode(Str::limit(trim($name), 40, ''));

        return 'https://ui-avatars.com/api/?name='.$enc.'&size=256&format=png&bold=true&background=0f4c3a&color=ffffff';
    }

    /**
     * @return array{0: string, 1: string}|null [binary, extension jpg|png|webp]
     */
    private function downloadImageIfAllowed(string $url): ?array
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || ! $this->hostAllowed($host)) {
            return null;
        }

        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(25)
                ->get($url);

            if (! $resp->successful()) {
                return null;
            }

            $binary = $resp->body();
            if (strlen($binary) < 200) {
                return null;
            }

            $ct = strtolower((string) $resp->header('Content-Type'));
            if (str_contains($ct, 'jpeg') || str_contains($ct, 'jpg')) {
                return [$binary, 'jpg'];
            }
            if (str_contains($ct, 'png')) {
                return [$binary, 'png'];
            }
            if (str_contains($ct, 'webp')) {
                return [$binary, 'webp'];
            }

            $path = (string) ($parts['path'] ?? '');
            if (str_ends_with(strtolower($path), '.png')) {
                return [$binary, 'png'];
            }
            if (preg_match('/\.jpe?g$/i', $path)) {
                return [$binary, 'jpg'];
            }
            if (str_ends_with(strtolower($path), '.webp')) {
                return [$binary, 'webp'];
            }

            return [$binary, 'png'];
        } catch (\Throwable) {
            return null;
        }
    }

    private function hostAllowed(string $host): bool
    {
        if ($host === 'upload.wikimedia.org') {
            return true;
        }
        if (str_ends_with($host, '.upload.wikimedia.org')) {
            return true;
        }
        if ($host === 'ui-avatars.com') {
            return true;
        }

        return false;
    }
}
