@extends('backend.layouts.master')

@section('title')
{{ __('Lineup - vs ' . !$match ? $opponentTeamName : '') }}
@endsection
@php
$usr = Auth::guard('admin')->user();
@endphp
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<style>
    body {
        background: linear-gradient(135deg, #0f4c3a 0%, #1a7f64 100%);
        min-height: 100vh;
        font-size: 13px;
    }

    .page-title-area {
        background: linear-gradient(135deg, rgba(15, 76, 58, 0.95), rgba(26, 127, 100, 0.95));
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 15px;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-title-area .row {
        align-items: center;
    }

    .page-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 5px;
    }

    /* Beat global backend-ui-improvements / styles.css .page-title-area .page-title */
    .page-title-area h4.page-title,
    .page-title-area .page-title {
        color: #ffffff !important;
    }

    .page-title-area p {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 2px;
        font-size: 12px;
    }

    .main-content-inner {
        background: rgba(255, 255, 255, 0.97);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .alert {
        border-radius: 8px;
        border: none;
        font-weight: 500;
        padding: 10px 15px;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .alert-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .alert-info {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }

    .lineup-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    /* Interactive pitch (new UI) */
    .pitch-wrap {
        display: grid;
        grid-template-columns: 1.3fr 0.7fr;
        gap: 15px;
        margin-top: 15px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .pitch-wrap { grid-template-columns: 1fr; }
    }
    .pitch-card {
        background: #0f4c3a;
        border-radius: 14px;
        padding: 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.18);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .pitch-card:before {
        content: "";
        position: absolute;
        inset: 0;
        background:
          radial-gradient(circle at 50% 50%, rgba(255,255,255,0.10), transparent 55%),
          linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0));
        pointer-events: none;
    }
    .pitch {
        position: relative;
        aspect-ratio: 16 / 10;
        min-height: 380px;
        border-radius: 12px;
        background: linear-gradient(90deg, rgba(255,255,255,0.06) 0 50%, rgba(0,0,0,0.04) 50% 100%);
        border: 2px solid rgba(255,255,255,0.30);
        overflow: hidden;
    }
    .pitch .line {
        position: absolute;
        inset: 0;
        border: 2px solid rgba(255,255,255,0.35);
        border-radius: 10px;
        margin: 10px;
        pointer-events: none;
    }
    .pitch .midline {
        position: absolute;
        top: 0; bottom: 0;
        left: 50%;
        width: 2px;
        background: rgba(255,255,255,0.35);
        transform: translateX(-1px);
        pointer-events: none;
    }
    .pitch .circle {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.35);
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .slot-grid {
        position: absolute;
        inset: 12px;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: repeat(4, 1fr);
        gap: 10px;
        padding: 6px;
    }
    .slot {
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 12px;
        padding: 8px 10px;
        cursor: pointer;
        transition: transform .12s ease, background .12s ease, border-color .12s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 62px;
        min-width: 0;
        user-select: none;
    }
    .slot:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,0.14);
        border-color: rgba(255,255,255,0.40);
    }
    .slot .meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 11px;
        opacity: .95;
    }
    .slot .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 18px;
        padding: 0 8px;
        border-radius: 999px;
        background: rgba(0,0,0,0.20);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.18);
        font-weight: 700;
        font-size: 10px;
    }
    .slot .name {
        font-weight: 800;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .slot .hint {
        font-size: 11px;
        opacity: .85;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Keep pitch boxes uniform height/alignment */
    .slot {
        height: 74px;
        justify-content: flex-start;
    }
    .slot .meta { min-height: 18px; }
    .slot .name { min-height: 14px; }
    .slot .hint { min-height: 13px; }

    /* Mobile pitch alignment: prevent horizontal overflow + tighten spacing */
    @media (max-width: 576px) {
        .pitch {
            min-height: 320px;
        }
        .slot-grid {
            inset: 8px;
            gap: 8px;
            padding: 4px;
        }
        .slot {
            padding: 6px 8px;
            min-height: 54px;
            height: 64px;
        }
        .slot .meta { font-size: 10px; }
        .slot .badge { min-width: 30px; height: 16px; font-size: 9px; padding: 0 6px; }
        .slot .name { font-size: 11px; }
        .slot .hint { font-size: 10px; }
    }
    .slot.empty .name { opacity: .85; font-weight: 700; }
    .slot.empty .hint { opacity: .75; }

    /* slot placement */
    .slot[data-slot="gk"] { grid-column: 1 / span 5; justify-self: center; width: min(360px, 100%); }
    .slot[data-slot="p2"] { grid-row: 2; grid-column: 1 / span 2; }
    .slot[data-slot="p3"] { grid-row: 2; grid-column: 3; }
    .slot[data-slot="p4"] { grid-row: 2; grid-column: 4 / span 2; }
    .slot[data-slot="p5"] { grid-row: 3; grid-column: 1; }
    .slot[data-slot="p6"] { grid-row: 3; grid-column: 2; }
    .slot[data-slot="p7"] { grid-row: 3; grid-column: 3; }
    .slot[data-slot="p8"] { grid-row: 3; grid-column: 4; }
    .slot[data-slot="p9"] { grid-row: 3; grid-column: 5; }
    .slot[data-slot="p10"] { grid-row: 4; grid-column: 2 / span 2; }
    .slot[data-slot="p11"] { grid-row: 4; grid-column: 4 / span 2; }

    .bench-card {
        background: #f8f9fa;
        border-radius: 14px;
        padding: 14px;
        border: 1px solid #e9ecef;
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }
    .bench-title {
        display:flex;
        align-items:center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        color: #0f4c3a;
    }
    .bench-title h5 { margin:0; font-weight: 800; font-size: 1rem; display:flex; align-items:center; gap:8px;}
    .mini-count {
        font-size: 12px;
        background: #0f4c3a;
        color: #fff;
        border-radius: 999px;
        padding: 2px 10px;
        font-weight: 800;
    }
    .bench-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .bench-slot { background: #fff; border: 1px dashed #cfd8dc; border-radius: 12px; padding: 10px; cursor: pointer; }
    .bench-slot:hover { border-style: solid; border-color: #0f4c3a33; }

    /* Player picker modal */
    .picker-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .picker-backdrop.show { display: flex; }
    .picker {
        width: min(720px, 96vw);
        max-height: min(78vh, 720px);
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0,0,0,0.35);
    }
    .picker-head {
        padding: 12px 14px;
        background: #0f4c3a;
        color: #fff;
        display:flex;
        align-items:center;
        justify-content: space-between;
        gap: 12px;
    }
    .picker-head .title { font-weight: 800; }
    .picker-head button {
        border: none;
        background: rgba(255,255,255,0.12);
        color: #fff;
        border-radius: 10px;
        padding: 6px 10px;
        font-weight: 800;
        cursor: pointer;
    }
    .picker-body { padding: 12px 14px; }
    .picker-search {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        outline: none;
    }
    .picker-list {
        overflow: auto;
        max-height: calc(78vh - 130px);
        border: 1px solid #eef1f4;
        border-radius: 12px;
    }
    .picker-item {
        display:flex;
        align-items:center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #f2f4f7;
        cursor: pointer;
    }
    .picker-item:last-child { border-bottom: none; }
    .picker-item:hover { background: #f8fafc; }
    .picker-item.disabled { opacity: .45; cursor: not-allowed; }
    .picker-left { display:flex; flex-direction: column; gap:2px; }
    .picker-name { font-weight: 800; color: #111; }
    .picker-sub { font-size: 12px; color: #555; }
    .picker-tag { font-size: 11px; font-weight: 800; color: #0f4c3a; background:#e7f7f2; border: 1px solid #bfe9dc; padding:2px 10px; border-radius: 999px; white-space: nowrap; }
    .picker-actions { display:flex; gap:8px; }
    .picker-clear { border: 1px solid #e5e7eb; background:#fff; border-radius: 10px; padding: 6px 10px; font-weight: 800; cursor:pointer; }
    .picker-clear:hover { background:#f8fafc; }

    .lineup-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        border: 1px solid #e9ecef;
    }

    .lineup-section h5 {
        color: #0f4c3a;
        font-size: 1rem;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
    }

    .section-icon {
        width: 22px;
        height: 22px;
        background: #0f4c3a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 11px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group label {
        font-weight: 600;
        color: #0f4c3a;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
    }

    .position-number {
        background: #0f4c3a;
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        min-width: 20px;
    }

    .form-control.player-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 10px center;
        background-repeat: no-repeat;
        background-size: 14px;
        padding-right: 35px;
        min-height: 40px;
        line-height: 1.3;
    }

    .form-control.player-select:focus {
        outline: none;
        border-color: #0f4c3a;
        box-shadow: 0 0 0 2px rgba(15, 76, 58, 0.1);
    }

    .goalkeeper-section {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: white;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .goalkeeper-section label {
        color: white !important;
    }

    .goalkeeper-section .form-control.player-select {
        border-color: rgba(255, 255, 255, 0.3);
        background-color: rgba(255, 255, 255, 0.97);
    }

    .substitutes-section {
        background: #e8f5e8;
    }

    .substitutes-section .section-icon {
        background: #28a745;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0f4c3a, #1a7f64);
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(15, 76, 58, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0a3429, #146b54);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 76, 58, 0.35);
    }

    .deadline-warning {
        background: linear-gradient(45deg, #ff6b35, #f7931e);
        padding: 10px 15px;
        border-radius: 8px;
        margin: 15px 0;
        color: white;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.25);
        font-size: 13px;
    }

    .deadline-warning.blink-red {
        background: linear-gradient(45deg, #dc3545, #c82333);
        box-shadow: 0 2px 10px rgba(220, 53, 69, 0.35);
        animation: fpBlinkRed 1s infinite;
    }

    @keyframes fpBlinkRed {
        0%, 100% { filter: brightness(1); }
        50% { filter: brightness(1.35); }
    }

    .lineup-status {
        border-radius: 10px;
        padding: 12px 16px;
        margin: 12px 0 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid #e9ecef;
    }
    .lineup-status .meta {
        font-size: 12px;
        font-weight: 600;
        opacity: .9;
    }
    .lineup-status.submitted {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-color: #b7dfc1;
    }
    .lineup-status.submitted.association-may-edit {
        background: linear-gradient(135deg, #d1ecf1, #bee5eb);
        color: #0c5460;
        border-color: #abdde5;
    }
    .lineup-status.not-submitted {
        background: linear-gradient(135deg, #fff3cd, #ffe8a1);
        color: #856404;
        border-color: #ffe08a;
    }

    .admin-override-notice {
        background: linear-gradient(135deg, #17a2b8, #138496);
        padding: 10px 15px;
        border-radius: 8px;
        margin: 15px 0;
        color: white;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
    }

    .admin-badge {
        background: rgba(255, 255, 255, 0.25);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .formation-visual {
        background: linear-gradient(to bottom, #2d8f47, #4caf50);
        border-radius: 10px;
        padding: 15px;
        margin: 15px 0;
        position: relative;
        min-height: 140px;
        overflow: hidden;
    }

    .formation-visual::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
        background-size: 15px 15px;
    }

    .field-lines {
        position: absolute;
        top: 10%;
        left: 10%;
        right: 10%;
        bottom: 10%;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
    }

    .field-lines::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%);
    }

    .field-lines::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 60px;
        height: 60px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    .formation-preview {
        position: relative;
        z-index: 2;
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 12px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        align-items: center;
        min-height: 120px;
    }

    .player-avatar {
        background: rgba(255, 255, 255, 0.97);
        border-radius: 50%;
        width: 55px;
        height: 55px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #0f4c3a;
        font-size: 9px;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
        position: relative;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .player-avatar:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .player-avatar::before {
        content: '👤';
        font-size: 20px;
        margin-bottom: 2px;
    }

    .player-name {
        font-size: 8px;
        text-align: center;
        line-height: 1;
        max-width: 50px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0 2px;
    }

    .player-count {
        font-size: 11px;
        color: #6c757d;
        margin-left: 6px;
        font-weight: 500;
    }

    .form-control.player-select.selected {
        border-color: #28a745;
        background: #f8fff9;
    }

    .form-control.player-select option:disabled {
        opacity: 0.5;
    }

    /* Compact spacing */
    .mt-3 {
        margin-top: 15px !important;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .lineup-container {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .page-title {
            font-size: 1.1rem;
        }

        .page-title-area {
            padding: 12px 15px;
        }

        .main-content-inner {
            padding: 15px;
        }

        .formation-visual {
            min-height: 100px;
        }

        .player-avatar {
            width: 45px;
            height: 45px;
        }

        .player-avatar::before {
            font-size: 16px;
        }

        .player-name {
            font-size: 7px;
        }
    }

    /* Even more compact for larger screens */
    @media (min-width: 1200px) {
        .lineup-container {
            gap: 20px;
        }

        .lineup-section {
            padding: 18px;
        }
    }

    /* Tighter form controls */
    select.form-control.player-select {
        height: 40px;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    /* Compact buttons */
    button[type="submit"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* Readonly submitted lineup (no pitch) */
    .lineup-readonly-wrap {
        margin-top: 18px;
    }
    .lineup-readonly-hero {
        background: linear-gradient(135deg, #0f4c3a 0%, #1a7f64 55%, #2d9d7a 100%);
        color: #fff;
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 18px;
        box-shadow: 0 12px 32px rgba(15, 76, 58, 0.22);
        border: 1px solid rgba(255,255,255,0.2);
    }
    .lineup-readonly-hero h3 {
        margin: 0 0 6px;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: #ffffff;
    }
    .lineup-readonly-hero p {
        margin: 0;
        font-size: 13px;
        color: #94a3b8;
    }
    .lineup-readonly-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .lineup-readonly-grid { grid-template-columns: 1fr; }
    }
    .lineup-readonly-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e8ece9;
        box-shadow: 0 8px 28px rgba(15, 76, 58, 0.08);
        overflow: hidden;
    }
    .lineup-readonly-card--subs {
        background: linear-gradient(180deg, #f6fbf9 0%, #fff 100%);
        border-color: #d4ebe3;
    }
    .lineup-readonly-card__head {
        padding: 14px 16px;
        background: linear-gradient(135deg, #f0faf7, #e8f5f0);
        border-bottom: 1px solid #dceee6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .lineup-readonly-card--subs .lineup-readonly-card__head {
        background: linear-gradient(135deg, #e8f6ec, #dff3e5);
        border-bottom-color: #c5e6d0;
    }
    .lineup-readonly-card__head h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f4c3a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .lineup-readonly-rows {
        padding: 6px 0;
    }
    .lineup-readonly-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f3f1;
        transition: background 0.15s ease;
    }
    .lineup-readonly-row:last-child { border-bottom: none; }
    .lineup-readonly-row:hover { background: #fafcfb; }
    .lineup-readonly-badge {
        flex-shrink: 0;
        min-width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        background: linear-gradient(135deg, #0f4c3a, #1a7f64);
        color: #fff;
        box-shadow: 0 4px 12px rgba(15, 76, 58, 0.2);
    }
    .lineup-readonly-card--subs .lineup-readonly-badge {
        background: linear-gradient(135deg, #2e8b57, #3cb371);
    }
    .lineup-readonly-body { flex: 1; min-width: 0; }
    .lineup-readonly-name {
        font-weight: 800;
        font-size: 14px;
        color: #1a2e28;
        line-height: 1.25;
    }
    .lineup-readonly-pos {
        font-size: 12px;
        color: #5c6f68;
        margin-top: 2px;
    }

    /* Editable list (replaces pitch) */
    .lineup-list-editor {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 16px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .lineup-list-editor { grid-template-columns: 1fr; }
    }
    .lineup-list-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e8ece9;
        box-shadow: 0 8px 28px rgba(15, 76, 58, 0.07);
        overflow: hidden;
    }
    .lineup-list-card--subs {
        background: linear-gradient(180deg, #fafdfb 0%, #fff 100%);
    }
    .lineup-list-card__head {
        padding: 14px 16px;
        background: linear-gradient(135deg, #0f4c3a, #1a7f64);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-weight: 800;
        font-size: 1rem;
    }
    .lineup-list-card--subs .lineup-list-card__head {
        background: linear-gradient(135deg, #2e8b57, #3daa6a);
    }
    .lineup-list-card__head .mini-count {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.35);
    }
    .lineup-list-rows { padding: 8px 0; }
    .lineup-list-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 10px 8px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px dashed #cfd8dc;
        background: #fafcfb;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        min-height: 56px;
    }
    .lineup-list-row:hover {
        border-style: solid;
        border-color: #0f4c3a55;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 76, 58, 0.08);
    }
    .lineup-list-row:not(.empty) {
        border-style: solid;
        border-color: #bfe9dc;
        background: linear-gradient(90deg, #f0faf7, #fff);
    }
    .lineup-list-row .meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .lineup-list-row .badge {
        min-width: 36px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #0f4c3a;
        color: #fff;
        font-weight: 800;
        font-size: 12px;
    }
    .lineup-list-row .name {
        font-weight: 800;
        font-size: 14px;
        color: #1a2e28;
    }
    .lineup-list-row .hint {
        font-size: 12px;
        color: #6b7c76;
        margin-top: 2px;
    }
    .lineup-list-row .slot-body { flex: 1; min-width: 0; }
</style>
@endsection

@section('admin-content')

@if(!$match)
<!-- No Match Available -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h4 class="page-title">🏆 {{ __('Match Lineup') }}</h4>
        </div>
        <div class="col-sm-4 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="no-match-container">
    <div class="main-content-inner">
        <div class="no-match-icon">⚽</div>
        <h2 class="no-match-title">{{ __('No Upcoming Match') }}</h2>
        <p class="no-match-description">
            {{ __('There are currently no scheduled matches available for lineup submission.') }}
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-back-dashboard">
            <span>←</span>
            {{ __('Back to Dashboard') }}
        </a>
    </div>
</div>
@else

@php
$now = \Carbon\Carbon::now('Asia/Kuala_Lumpur');
$matchDate = \Carbon\Carbon::createFromTimestamp($match->date)->setTimezone('Asia/Kuala_Lumpur');
$submissionDeadline = $matchDate->copy()->subHours(24);
$deadlinePassed = $now->gt($submissionDeadline);

// Check if user has permission to bypass deadline
$canBypassDeadline = $usr->can('club.create');
$isEditingAllowed = !$deadlinePassed || $canBypassDeadline;

$starterIds = $existingLineup ? [
    $existingLineup->gk,
    $existingLineup->player1,
    $existingLineup->player2,
    $existingLineup->player3,
    $existingLineup->player4,
    $existingLineup->player5,
    $existingLineup->player6,
    $existingLineup->player7,
    $existingLineup->player8,
    $existingLineup->player9,
    $existingLineup->player10,
] : [];

$subIds = $existingLineup ? [
    $existingLineup->sub1,
    $existingLineup->sub2,
    $existingLineup->sub3,
    $existingLineup->sub4,
    $existingLineup->sub5,
    $existingLineup->sub6,
    $existingLineup->sub7,
] : [];

$lineupSubmitted = (bool) $existingLineup;
$canOverrideSubmittedLineup = $usr->hasRole('Association Manager') || $usr->can('association.view');
$canEditLineup = (! $lineupSubmitted && $isEditingAllowed) || ($lineupSubmitted && $canOverrideSubmittedLineup);

$playerById = $players->keyBy('id');
$readonlyStarters = [];
$readonlySubs = [];
if ($existingLineup) {
    $starterMap = [
        ['gk', 'GK'],
        ['player1', '2'],
        ['player2', '3'],
        ['player3', '4'],
        ['player4', '5'],
        ['player5', '6'],
        ['player6', '7'],
        ['player7', '8'],
        ['player8', '9'],
        ['player9', '10'],
        ['player10', '11'],
    ];
    foreach ($starterMap as [$field, $label]) {
        $pid = $existingLineup->{$field};
        $p = $pid ? $playerById->get((int) $pid) : null;
        $readonlyStarters[] = [
            'label' => $label,
            'name' => $p->name ?? '—',
            'position' => $p->position ?? '',
        ];
    }
    for ($si = 1; $si <= 7; $si++) {
        $field = 'sub'.$si;
        $pid = $existingLineup->{$field};
        $p = $pid ? $playerById->get((int) $pid) : null;
        $readonlySubs[] = [
            'label' => 'S'.$si,
            'name' => $p->name ?? '—',
            'position' => $p->position ?? '',
        ];
    }
}
@endphp

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h4 class="page-title">⚽ {{ __('Lineup') }} - {{ __('vs') }} {{ $opponentTeamName }}</h4>
            <p>📅 {{ $matchDate->format('d M Y H:i') }} • ⏰ {{ __('Deadline:') }} {{ $submissionDeadline->format('d M Y H:i') }}</p>
        </div>
        <div class="col-sm-4 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    @if ($errors->has('duplicate'))
    <div class="alert alert-danger">
        {{ $errors->first('duplicate') }}
    </div>
    @endif
    @if ($errors->has('locked'))
    <div class="alert alert-danger">
        {{ $errors->first('locked') }}
    </div>
    @endif

    <div class="lineup-status {{ $existingLineup ? 'submitted' : 'not-submitted' }}{{ $existingLineup && $canOverrideSubmittedLineup ? ' association-may-edit' : '' }}">
        <div>
            @if ($existingLineup)
                @if ($canOverrideSubmittedLineup)
                🔓 {{ __('Lineup submitted — association may update') }}
                @else
                🔒 {{ __('Lineup submitted — read only') }}
                @endif
                <div class="meta">
                    {{ __('Submitted') }}: {{ optional($existingLineup->updated_at)->timezone('Asia/Kuala_Lumpur')->format('d M Y H:i') ?? '-' }}
                </div>
            @else
                ⚠️ {{ __('Lineup not submitted yet') }}
                <div class="meta">
                    {{ __('Save your lineup before the deadline') }} ({{ $submissionDeadline->format('d M Y H:i') }})
                </div>
            @endif
        </div>
        <div class="meta">
            {{ __('Match') }}: {{ $matchDate->format('d M Y H:i') }}
        </div>
    </div>

    @if (!$lineupSubmitted)
        @if ($deadlinePassed)
            @if ($canBypassDeadline)
            <div class="admin-override-notice">
                <span class="admin-badge">🔓 ADMIN</span>
                <span>{{ __('Special permission: submit after deadline') }}</span>
            </div>
            @else
            <div class="deadline-warning blink-red">
                🚫 {{ __('Submission closed') }} - {{ $submissionDeadline->format('d M Y H:i') }}
            </div>
            @endif
        @else
        <div class="deadline-warning" id="countdown-timer">
            ⚠️ {{ __('Lineup submission deadline approaching') }}
        </div>
        @endif
    @endif

    @if ($lineupSubmitted && ! $canOverrideSubmittedLineup)
        <div class="lineup-readonly-wrap">
            <div class="lineup-readonly-hero">
                <h3>{{ __('Your submitted squad') }}</h3>
                <p>{{ __('This lineup is locked. Contact the league if you need a correction.') }}</p>
            </div>
            <div class="lineup-readonly-grid">
                <div class="lineup-readonly-card">
                    <div class="lineup-readonly-card__head">
                        <h4>⚽ {{ __('Starting XI') }}</h4>
                        <span class="mini-count">11</span>
                    </div>
                    <div class="lineup-readonly-rows">
                        @foreach ($readonlyStarters as $row)
                        <div class="lineup-readonly-row">
                            <div class="lineup-readonly-badge">{{ $row['label'] }}</div>
                            <div class="lineup-readonly-body">
                                <div class="lineup-readonly-name">{{ $row['name'] }}</div>
                                @if (!empty($row['position']))
                                <div class="lineup-readonly-pos">{{ $row['position'] }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="lineup-readonly-card lineup-readonly-card--subs">
                    <div class="lineup-readonly-card__head">
                        <h4>🔄 {{ __('Substitutes') }}</h4>
                        <span class="mini-count">7</span>
                    </div>
                    <div class="lineup-readonly-rows">
                        @foreach ($readonlySubs as $row)
                        <div class="lineup-readonly-row">
                            <div class="lineup-readonly-badge">{{ $row['label'] }}</div>
                            <div class="lineup-readonly-body">
                                <div class="lineup-readonly-name">{{ $row['name'] }}</div>
                                @if (!empty($row['position']))
                                <div class="lineup-readonly-pos">{{ $row['position'] }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @elseif ($canEditLineup)
    <form action="{{ route('admin.lineup.save') }}" method="POST" id="lineupForm">
        @csrf
        <input type="hidden" name="match_id" value="{{ $match->id }}">
        <input type="hidden" name="club_id" value="{{ $club_id }}">

        <div style="display:none;">
            @for ($i = 1; $i <= 11; $i++)
                <select name="starters[]" class="form-control player-select" data-position="{{ $i }}">
                    <option value="">{{ __('Select Player') }}</option>
                    @foreach ($players as $player)
                        @if ($i == 1 && $player->position == 'Goalkeeper')
                            <option value="{{ $player->id }}" {{ (old("starters.$i") ?? ($starterIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                                {{ $player->name }} - {{ $player->position }}
                            </option>
                        @elseif ($i != 1 && $player->position != 'Goalkeeper')
                            <option value="{{ $player->id }}" {{ (old("starters.$i") ?? ($starterIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                                {{ $player->name }} - {{ $player->position }}
                            </option>
                        @endif
                    @endforeach
                </select>
            @endfor
            @for ($i = 1; $i <= 7; $i++)
                <select name="subs[]" class="form-control player-select sub-select" data-position="sub{{ $i }}">
                    <option value="">{{ __('Select Player') }}</option>
                    @foreach ($players as $player)
                        <option value="{{ $player->id }}" {{ (old("subs.$i") ?? ($subIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                            {{ $player->name }} - {{ $player->position }}
                        </option>
                    @endforeach
                </select>
            @endfor
        </div>

        <div class="lineup-list-editor">
            <div class="lineup-list-card">
                <div class="lineup-list-card__head">
                    <span>{{ __('Starting XI') }}</span>
                    <div class="mini-count"><span id="starter-count-inline">0</span>/11</div>
                </div>
                <div class="lineup-list-rows" id="pitchSlots">
                    <div class="lineup-list-row slot empty" data-slot="gk" data-position="1">
                        <div class="meta"><span class="badge">GK</span></div>
                        <div class="slot-body">
                            <div class="name">{{ __('Select Goalkeeper') }}</div>
                            <div class="hint">{{ __('Tap to choose') }}</div>
                        </div>
                    </div>
                    @for ($li = 2; $li <= 11; $li++)
                    <div class="lineup-list-row slot empty" data-slot="p{{ $li }}" data-position="{{ $li }}">
                        <div class="meta"><span class="badge">{{ $li }}</span></div>
                        <div class="slot-body">
                            <div class="name">{{ __('Select Player') }}</div>
                            <div class="hint">{{ __('Tap to choose') }}</div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
            <div class="lineup-list-card lineup-list-card--subs">
                <div class="lineup-list-card__head">
                    <span>{{ __('Substitutes') }}</span>
                    <div class="mini-count"><span id="sub-count-inline">0</span>/7</div>
                </div>
                <div class="lineup-list-rows" id="benchSlots">
                    @for ($i = 1; $i <= 7; $i++)
                    <div class="lineup-list-row slot empty" data-position="sub{{ $i }}" data-slot="sub{{ $i }}">
                        <div class="meta"><span class="badge">S{{ $i }}</span></div>
                        <div class="slot-body">
                            <div class="name">{{ __('Select Sub') }} {{ $i }}</div>
                            <div class="hint">{{ __('Tap to choose') }}</div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            ℹ️ {{ __('You can save once at least 14 players are selected (11 starters + minimum 3 substitutes).') }}
        </div>
        <button type="submit" class="btn btn-primary mt-3" id="saveButton">
            @if ($lineupSubmitted && $canOverrideSubmittedLineup)
            💾 {{ __('Update lineup') }}
            @else
            💾 {{ __('Save Lineup') }}
            @endif
        </button>
        @if ($deadlinePassed && $canBypassDeadline)
        <div class="alert alert-info mt-3">
            ℹ️ {{ __('Submitting after deadline with admin privileges') }}
        </div>
        @endif
    </form>

    <div class="picker-backdrop" id="playerPicker">
        <div class="picker" role="dialog" aria-modal="true" aria-label="{{ __('Select player') }}">
            <div class="picker-head">
                <div class="title" id="pickerTitle">{{ __('Select Player') }}</div>
                <div class="picker-actions">
                    <button type="button" class="picker-clear" id="pickerClear">{{ __('Clear') }}</button>
                    <button type="button" id="pickerClose">{{ __('Close') }}</button>
                </div>
            </div>
            <div class="picker-body">
                <input type="text" class="picker-search" id="pickerSearch" placeholder="{{ __('Search player...') }}">
                <div class="picker-list" id="pickerList"></div>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-danger mt-3">
            🚫 {{ __('Submission closed') }} - {{ $submissionDeadline->format('d M Y H:i') }}
        </div>
    @endif
</div>

@endif

@endsection

@if($match && $canEditLineup)
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.player-select');
        const starterSelects = document.querySelectorAll('.player-select:not(.sub-select)');
        const subSelects = document.querySelectorAll('.sub-select');
        const starterCount = document.getElementById('starter-count');
        const subCount = document.getElementById('sub-count');
        const starterCountInline = document.getElementById('starter-count-inline');
        const subCountInline = document.getElementById('sub-count-inline');
        const saveButton = document.getElementById('saveButton');
        const fpSaveReadyLabel = @json(($lineupSubmitted && $canOverrideSubmittedLineup) ? __('Update lineup') : __('Save Lineup'));
        const formationDisplay = document.getElementById('formation-display');

        const pickerBackdrop = document.getElementById('playerPicker');
        const pickerList = document.getElementById('pickerList');
        const pickerSearch = document.getElementById('pickerSearch');
        const pickerTitle = document.getElementById('pickerTitle');
        const pickerClose = document.getElementById('pickerClose');
        const pickerClear = document.getElementById('pickerClear');

        // Build a players index from the existing selects (options appear multiple times; de-dupe by id).
        const allPlayers = Array.from(document.querySelectorAll('select.player-select option'))
            .filter(o => o.value && o.value !== '')
            .map(o => {
                const text = (o.textContent || '').trim();
                const parts = text.split(' - ');
                const name = (parts[0] || text).trim().split(' (')[0];
                const position = (parts[1] || '').trim();
                return { id: o.value, name, position, label: text };
            })
            .filter((p, idx, arr) => arr.findIndex(x => x.id === p.id) === idx);

        let activeSelect = null;
        let activeSlotEl = null;
        let activeKind = null; // 'gk' | 'outfield' | 'sub'

        function updateCounts() {
            const selectedStarters = Array.from(starterSelects).filter(select => select.value !== '').length;
            const selectedSubs = Array.from(subSelects).filter(select => select.value !== '').length;
            const totalSelected = selectedStarters + selectedSubs;

            if (starterCount) starterCount.textContent = `(${selectedStarters}/11)`;
            if (subCount) subCount.textContent = `(${selectedSubs}/7)`;
            if (starterCountInline) starterCountInline.textContent = String(selectedStarters);
            if (subCountInline) subCountInline.textContent = String(selectedSubs);

            if (saveButton) {
                const startersComplete = selectedStarters === 11;
                const minSubsMet = selectedSubs >= 3;
                const canSave = startersComplete && minSubsMet; // 11 starters + min 3 subs (>=14 total)

                saveButton.disabled = !canSave;
                saveButton.innerHTML = canSave
                    ? `💾 ${fpSaveReadyLabel} (${totalSelected}/18)`
                    : `💾 {{ __("Select at least 14") }} (${totalSelected}/18)`;
            }
        }

        function currentSelectedIds() {
            return new Set(Array.from(selects).map(s => s.value).filter(v => v && v !== ''));
        }

        function slotLabelForSelect(selectEl) {
            const pos = String(selectEl.dataset.position || '');
            if (pos === '1') return { badge: 'GK', kind: 'gk', title: 'Select Goalkeeper' };
            if (pos.startsWith('sub')) return { badge: pos.replace('sub', 'S'), kind: 'sub', title: 'Select Substitute' };
            return { badge: pos, kind: 'outfield', title: 'Select Player' };
        }

        function setSlotDisplay(slotEl, player) {
            if (!slotEl) return;
            if (!player) {
                slotEl.classList.add('empty');
                const pos = String(slotEl.dataset.position || '');
                if (pos === '1') slotEl.querySelector('.name').textContent = 'Select Goalkeeper';
                else if (pos.startsWith('sub')) slotEl.querySelector('.name').textContent = `Select Sub ${pos.replace('sub', '')}`;
                else slotEl.querySelector('.name').textContent = 'Select Player';
                slotEl.querySelector('.hint').textContent = 'Tap to choose';
                return;
            }
            slotEl.classList.remove('empty');
            slotEl.querySelector('.name').textContent = player.name;
            slotEl.querySelector('.hint').textContent = player.position ? player.position : 'Selected';
        }

        function syncSlotsFromSelects() {
            for (let i = 1; i <= 11; i++) {
                const sel = document.querySelector(`select.player-select[data-position="${i}"]`);
                const slotEl = document.querySelector(`.slot[data-position="${i}"]`);
                if (!sel || !slotEl) continue;
                const player = allPlayers.find(p => p.id === sel.value) || null;
                setSlotDisplay(slotEl, player);
            }
            for (let i = 1; i <= 7; i++) {
                const pos = `sub${i}`;
                const sel = document.querySelector(`select.player-select[data-position="${pos}"]`);
                const slotEl = document.querySelector(`.slot[data-position="${pos}"]`);
                if (!sel || !slotEl) continue;
                const player = allPlayers.find(p => p.id === sel.value) || null;
                setSlotDisplay(slotEl, player);
            }
        }

        function renderPickerList(query) {
            if (!pickerList) return;
            const q = (query || '').toLowerCase().trim();
            const selected = currentSelectedIds();
            const currentVal = activeSelect ? activeSelect.value : '';

            const filtered = allPlayers.filter(p => {
                if (activeKind === 'gk' && p.position !== 'Goalkeeper') return false;
                if (activeKind === 'outfield' && p.position === 'Goalkeeper') return false;
                if (q === '') return true;
                return (p.name.toLowerCase().includes(q) || (p.position || '').toLowerCase().includes(q));
            });

            pickerList.innerHTML = '';
            if (filtered.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'picker-item disabled';
                empty.textContent = 'No players found';
                pickerList.appendChild(empty);
                return;
            }

            filtered.forEach(p => {
                const isTaken = selected.has(p.id) && p.id !== currentVal;
                const row = document.createElement('div');
                row.className = 'picker-item' + (isTaken ? ' disabled' : '');

                const left = document.createElement('div');
                left.className = 'picker-left';
                const nm = document.createElement('div');
                nm.className = 'picker-name';
                nm.textContent = p.name;
                const sub = document.createElement('div');
                sub.className = 'picker-sub';
                sub.textContent = p.position ? p.position : '';
                left.appendChild(nm);
                left.appendChild(sub);

                const tag = document.createElement('div');
                tag.className = 'picker-tag';
                tag.textContent = p.position || '';

                row.appendChild(left);
                row.appendChild(tag);

                if (!isTaken) {
                    row.addEventListener('click', function () {
                        if (!activeSelect) return;
                        activeSelect.value = p.id;
                        activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        closePicker();
                    });
                }

                pickerList.appendChild(row);
            });
        }

        function openPicker(selectEl, slotEl) {
            if (!pickerBackdrop || !pickerTitle || !pickerSearch) return;
            activeSelect = selectEl;
            activeSlotEl = slotEl;
            const lbl = slotLabelForSelect(selectEl);
            activeKind = lbl.kind;
            pickerTitle.textContent = lbl.kind === 'gk' ? 'Select Goalkeeper' : (lbl.kind === 'sub' ? `Select Substitute (${lbl.badge})` : `Select Player (#${lbl.badge})`);
            pickerSearch.value = '';
            renderPickerList('');
            pickerBackdrop.classList.add('show');
            setTimeout(() => pickerSearch.focus(), 0);
        }

        function closePicker() {
            if (!pickerBackdrop) return;
            pickerBackdrop.classList.remove('show');
            activeSelect = null;
            activeSlotEl = null;
            activeKind = null;
        }

        function updateDropdowns() {
            const selectedValues = Array.from(selects).map(select => select.value).filter(v => v !== '');

            selects.forEach(select => {
                const currentValue = select.value;

                Array.from(select.options).forEach(option => {
                    if (option.value === '' || option.value === currentValue) {
                        option.disabled = false;
                        option.style.opacity = '1';
                    } else if (selectedValues.includes(option.value)) {
                        option.disabled = true;
                        option.style.opacity = '0.5';
                    } else {
                        option.disabled = false;
                        option.style.opacity = '1';
                    }
                });

                if (select.value !== '') {
                    select.classList.add('selected');
                } else {
                    select.classList.remove('selected');
                }
            });

            updateCounts();
            updateFormationDisplay();
            syncSlotsFromSelects();
        }

        function updateFormationDisplay() {
            if (!formationDisplay) return;

            const selectedPlayers = Array.from(selects)
                .filter(select => select.value !== '')
                .map(select => {
                    const option = select.options[select.selectedIndex];
                    const playerName = option.text.split(' (')[0].split(' - ')[0];
                    return playerName;
                });

            if (selectedPlayers.length > 0) {
                const playerAvatars = selectedPlayers.map(name => `
                    <div class="player-avatar">
                        <div class="player-name">${name}</div>
                    </div>
                `).join('');

                formationDisplay.innerHTML = `
                    <div style="font-size: 12px; margin-bottom: 10px;">
                        <strong>${selectedPlayers.length}/18 {{ __('Selected') }}</strong>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
                        ${playerAvatars}
                    </div>
                `;
            } else {
                formationDisplay.innerHTML = `
                    <div style="font-size: 13px; opacity: 0.8;">
                        {{ __("Select players to preview") }}
                    </div>
                `;
            }
        }

        selects.forEach(select => {
            select.addEventListener('change', function() {
                updateDropdowns();
                if (this.value !== '') {
                    this.style.transform = 'scale(1.01)';
                    setTimeout(() => this.style.transform = 'scale(1)', 120);
                }
            });
        });

        // Pitch/bench slot click -> open picker
        document.querySelectorAll('.slot[data-position]').forEach(slotEl => {
            slotEl.addEventListener('click', function() {
                if (!@json($canEditLineup)) return;
                const pos = slotEl.dataset.position;
                const selectEl = document.querySelector(`select.player-select[data-position="${pos}"]`);
                if (!selectEl) return;
                openPicker(selectEl, slotEl);
            });
        });

        // picker events
        if (pickerClose) pickerClose.addEventListener('click', closePicker);
        if (pickerBackdrop) {
            pickerBackdrop.addEventListener('click', function(e) {
                if (e.target === pickerBackdrop) closePicker();
            });
        }
        if (pickerSearch) {
            pickerSearch.addEventListener('input', function() {
                renderPickerList(pickerSearch.value);
            });
        }
        if (pickerClear) {
            pickerClear.addEventListener('click', function() {
                if (!activeSelect) return;
                activeSelect.value = '';
                activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                closePicker();
            });
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && pickerBackdrop && pickerBackdrop.classList.contains('show')) {
                closePicker();
            }
        });

        if (document.getElementById('lineupForm')) {
            document.getElementById('lineupForm').addEventListener('submit', function(e) {
                if (saveButton) {
                    saveButton.innerHTML = '⏳ {{ __("Saving...") }}';
                    saveButton.disabled = true;
                }
            });
        }

        @if(!$deadlinePassed)
        function updateCountdown() {
            const deadline = new Date('{{ $submissionDeadline->toISOString() }}');
            const countdownElement = document.getElementById('countdown-timer');

            if (!countdownElement) return;

            function updateTimer() {
                const now = new Date();
                const timeDiff = deadline - now;

                if (timeDiff > 0) {
                    const hours = Math.floor(timeDiff / (1000 * 60 * 60));
                    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                    countdownElement.innerHTML = `⚠️ {{ __('Closes in') }} ${hours}h ${minutes}m`;
                    // Blink red when within 1 hour of deadline
                    if (timeDiff <= (60 * 60 * 1000)) {
                        countdownElement.classList.add('blink-red');
                    } else {
                        countdownElement.classList.remove('blink-red');
                    }
                } else {
                    countdownElement.innerHTML = '🚫 {{ __("Deadline passed") }}';
                    countdownElement.classList.add('blink-red');
                    @if(!$canBypassDeadline)
                    if (saveButton) saveButton.disabled = true;
                    @endif
                }
            }

            updateTimer();
            setInterval(updateTimer, 60000);
        }

        updateCountdown();
        @endif

        updateDropdowns();
    });
</script>
@endsection
@endif