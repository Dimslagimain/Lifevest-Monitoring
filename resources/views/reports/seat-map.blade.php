<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Report {{ $registration }}</title>
    <style>
        /* PDF Specific Styles */
        @page {
            margin: 20px;
        }

        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #333;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
        }

        .airline-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-meta {
            font-size: 11pt;
            color: #555;
        }

        /* Sections */
        .cabin-section,
        .cockpit-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .avoid-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        h2 {
            font-size: 12pt;
            margin-bottom: 10px;
            background-color: #f0f0f0;
            padding: 5px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Grid Layout Fixes for PDF */
        .seat-grid,
        .cockpit-grid,
        .spare-grid {
            width: 100%;
            text-align: center;
        }

        .seat-row,
        .grid-header {
            display: block;
            margin-bottom: 5px;
            /* Consistent gap */
            white-space: nowrap;
            page-break-inside: avoid;
            /* CRITICAL: Prevents row splitting */
            line-height: 1;
            /* Fix vertical gaps */
        }

        /* Seat Card Refined */
        .seat-card {
            width: 65px;
            /* Wider for landscape date */
            height: 45px;
            /* Shorter height */
            border: 1px solid #ccc;
            background-color: #ffffff;
            display: inline-block;
            margin: 1px 2px;
            /* Small margin */
            text-align: center;
            vertical-align: top;
            padding: 2px 1px;
            border-radius: 4px;
            box-sizing: border-box;
            /* Proper sizing */
            overflow: hidden;
            white-space: normal;
        }

        .cockpit-seat {
            width: 85px;
            height: 55px;
        }

        .seat-label {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* Status Colors (Matching Web Dashboard) */
        .seat-card.status-active,
        .seat-card.status-safe {
            background-color: #e6f4ea;
            border-color: #10b981;
        }

        .seat-card.status-prolong {
            background-color: #fffbeb;
            border-color: #f59e0b;
        }

        .seat-card.status-warning {
            background-color: #fffbeb;
            border-color: #f59e0b;
        }

        .seat-card.status-critical {
            background-color: #fef2f2;
            border-color: #ef4444;
        }

        .seat-card.status-expired {
            background-color: #f5f3ff;
            border-color: #8b5cf6;
        }

        .seat-card.status-no-data {
            background-color: #f8fafc;
            border-color: #cbd5e0;
        }

        /* Grey */

        /* Typography inside seat */
        .seat-id {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
            /* Added spacing */
        }

        .seat-date {
            font-size: 11px !important;
            /* Slightly safer than 12px for full date */
            color: #222 !important;
            font-weight: 500 !important;
            line-height: 1.1 !important;
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            white-space: nowrap !important;
            /* Keep on one line */
            overflow: hidden;
            letter-spacing: -0.2px !important;
        }

        .col-header {
            width: 65px;
            /* Match new seat width */
            display: inline-block;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            margin: 1px 2px;
        }

        .row-number {
            width: 25px;
            /* Specific width for row num */
            display: inline-block;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            line-height: 45px;
            /* Match new seat height */
        }

        .row-label {
            width: 25px;
            display: inline-block;
            font-size: 8px;
        }

        .seat-placeholder {
            width: 69px;
            /* 65px + 4px margin */
            display: inline-block;
        }

        .aisle-gap {
            width: 10px;
            display: inline-block;
        }

        /* 38px + 4px margin */


        /* Spare Section */
        .spare-column {
            display: inline-block;
            vertical-align: top;
            width: 45%;
            border: 1px dashed #ccc;
            padding: 10px;
            margin: 1%;
        }

        .spare-items {
            min-height: 50px;
        }

        .empty-message {
            font-style: italic;
            color: #999;
            font-size: 9pt;
        }

        /* Interactive Elements Removal */
        button,
        .btn,
        .btn-delete-spare,
        .btn-add-spare {
            display: none !important;
        }

        /* Summary Box */
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .summary-item {
            display: inline-block;
            margin: 0 10px;
            font-size: 10pt;
        }

        /* =============================================
           PDF FIX: Attendant section flex layout fix
           DomPDF doesn't support flexbox, so sections 
           using inline style="display: flex" need override.
           This is a MINIMAL fix targeting only those sections.
           ============================================= */

        /* Force inline-block for column wrappers inside flex containers */
        .cabin-section>div[style]>div[style] {
            display: inline-block;
            vertical-align: top;
            margin: 0 20px;
        }

        /* Force inline-block for seat cards in nested flex containers */
        .cabin-section>div[style]>div[style]>div[style]>.seat-card {
            display: inline-block;
            margin: 0 3px;
        }
    </style>
</head>

<body>
    <div class="report-header">
        <div class="airline-title">
            @if($aircraft->airline_id == 1)
                GARUDA INDONESIA
            @elseif($aircraft->airline_id == 2)
                CITILINK
            @else
                Life Vest Tracker
            @endif
        </div>
        <div class="report-meta">
            Registration: <strong>{{ $registration }}</strong> &nbsp;|&nbsp;
            Type: <strong>{{ $aircraft->type }}</strong> &nbsp;|&nbsp;
            Last Update:
            <strong>{{ $seats->max('updated_at') ? \Carbon\Carbon::parse($seats->max('updated_at'))->format('d M Y H:i') : '-' }}</strong>
            &nbsp;|&nbsp;
            Printed: {{ date('d M Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        @php
            $stats = $seats->countBy('status');
        @endphp
        <div class="summary-item">Total Seats: <strong>{{ $seats->count() }}</strong></div>
        <div class="summary-item">Safe: <strong>{{ $stats['safe'] ?? 0 }}</strong></div>
        <div class="summary-item">Warning: <strong>{{ $stats['warning'] ?? 0 }}</strong></div>
        <div class="summary-item">Critical:
            <strong>{{ ($stats['critical'] ?? 0) + ($stats['expired'] ?? 0) }}</strong>
        </div>
    </div>

    <div class="content">
        <!-- Reusing the Blade Partial -->
        <!-- Note: We use 'include' dyanmically. The partial must assume $seats matches the structure. -->
        @if(View::exists('aircraft.partials.' . $aircraft->layout))
            @include('aircraft.partials.' . $aircraft->layout)
        @else
            <div style="text-align: center; padding: 50px; color: red;">
                Layout '{{ $aircraft->layout }}' not found.<br>
                Please create file: <code>resources/views/aircraft/partials/{{ $aircraft->layout }}.blade.php</code><br>
                <small>PDF export now uses the same layouts as the aircraft view.</small>
            </div>
        @endif
    </div>

    <div
        style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 5px;">
        Generated by Life Vest Tracker System
    </div>
</body>

</html>