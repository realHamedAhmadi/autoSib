<style>
    .automation-dashboard {
        width: 100%;
        padding-top: 1.25rem;
        padding-bottom: 2rem;
    }

    .dashboard-hero {
        padding: 1.25rem 1.5rem !important;
        border: 0 !important;
        border-radius: 14px !important;
        background: linear-gradient(135deg, #123b59 0%, #176b9d 55%, #2185d0 100%) !important;
        box-shadow: 0 12px 30px rgba(27, 79, 114, 0.18) !important;
    }

    .dashboard-hero .header,
    .dashboard-hero .sub.header,
    .dashboard-refresh-info {
        color: #fff !important;
    }

    .dashboard-hero .sub.header {
        margin-top: 0.45rem !important;
        opacity: 0.9;
        line-height: 1.8;
    }

    .dashboard-hero-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .dashboard-refresh-info {
        margin-top: 0.85rem;
        font-size: 12px;
        opacity: 0.9;
    }

    .dashboard-live-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
        background: #dcfce7;
        color: #166534;
    }

    .dashboard-live-status.is-loading {
        background: #fef3c7;
        color: #92400e;
    }

    .dashboard-live-status.is-error {
        background: #fee2e2;
        color: #991b1b;
    }

    .dashboard-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        flex: 0 0 8px;
    }

    .dashboard-stat-card {
        min-height: 120px;
        border: 0 !important;
        border-radius: 12px !important;
        box-shadow: 0 5px 18px rgba(34, 36, 38, 0.08) !important;
    }

    .dashboard-stat-icon {
        margin-bottom: 0.45rem;
        font-size: 1.45rem;
    }

    .dashboard-section {
        border: 0 !important;
        border-radius: 12px !important;
        box-shadow: 0 5px 18px rgba(34, 36, 38, 0.08) !important;
    }

    .dashboard-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(34, 36, 38, 0.08);
    }

    .dashboard-section-header .ui.header {
        margin: 0;
    }

    .dashboard-table-wrapper {
        width: 100%;
    }

    .automation-dashboard table {
        width: 100%;
        table-layout: fixed;
        border-radius: 8px !important;
        overflow: hidden;
    }

    .automation-dashboard table thead th {
        background: #f8fafc;
        color: #4b5563;
        font-size: 0.84rem;
        white-space: nowrap;
        text-align: right !important;
    }

    .automation-dashboard table th,
    .automation-dashboard table td {
        vertical-align: middle !important;
    }

    .automation-dashboard table tbody tr:hover {
        background-color: #f8fafc !important;
    }

    .run-id {
        color: #176b9d;
        font-family: monospace;
        font-size: 0.92rem;
        font-weight: bold;
        white-space: nowrap;
    }

    .progress-cell small {
        display: block;
        margin-top: 0.4rem;
        color: #6b7280;
        line-height: 1.7;
        text-align: right;
    }

    .run-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .run-actions .button {
        margin: 0 !important;
    }

    .retry-run-btn.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .empty-state {
        padding: 1.25rem !important;
        text-align: center;
        border-radius: 8px !important;
        line-height: 1.8;
    }

    .dashboard-toast-container {
        position: fixed;
        z-index: 9999;
        top: 1rem;
        left: 1rem;
        right: 1rem;
        display: flex;
        justify-content: center;
        pointer-events: none;
    }

    .dashboard-toast {
        width: min(420px, 100%);
        padding: 0.9rem 1rem;
        border-radius: 8px;
        color: #fff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.18);
        text-align: center;
        font-size: 0.9rem;
        line-height: 1.7;
        pointer-events: auto;
    }

    .dashboard-toast.success {
        background: #21ba45;
    }

    .dashboard-toast.error {
        background: #db2828;
    }

    @media only screen and (max-width: 991px) {
        .dashboard-section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-stat-card {
            min-height: 100px;
        }

        .automation-dashboard table th,
        .automation-dashboard table td {
            padding: 0.65rem 0.55rem !important;
            font-size: 0.84rem;
        }
    }

    @media only screen and (max-width: 767px) {
        .automation-dashboard {
            padding-top: 0.75rem;
            padding-bottom: 1.25rem;
        }

        .dashboard-hero {
            padding: 0.95rem !important;
        }

        .dashboard-hero .ui.header {
            font-size: 1.1rem !important;
            line-height: 1.6;
        }

        .dashboard-hero .sub.header {
            font-size: 0.8rem !important;
        }

        .dashboard-hero-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .dashboard-hero-actions .button,
        .dashboard-hero-actions a.button {
            width: 100%;
            margin: 0 !important;
        }

        .dashboard-live-status {
            width: 100%;
            box-sizing: border-box;
        }

        .dashboard-refresh-info {
            font-size: 11px;
        }

        .dashboard-section {
            padding: 0.9rem !important;
        }

        .automation-dashboard table,
        .automation-dashboard table tbody,
        .automation-dashboard table tr,
        .automation-dashboard table td {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .automation-dashboard table thead {
            display: none;
        }

        .automation-dashboard table {
            border: 0 !important;
            background: transparent !important;
        }

        .automation-dashboard table tbody {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .automation-dashboard table tr {
            padding: 0.85rem !important;
            border: 1px solid rgba(34, 36, 38, 0.12) !important;
            border-radius: 10px !important;
            background: #fff !important;
            box-shadow: 0 3px 12px rgba(34, 36, 38, 0.07);
        }

        .automation-dashboard table td {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.45rem 0 !important;
            border: 0 !important;
            border-bottom: 1px solid rgba(34, 36, 38, 0.08) !important;
            text-align: right !important;
            direction: rtl;
            word-break: break-word;
        }

        .automation-dashboard table td:last-child {
            border-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        .automation-dashboard table td::before {
            content: attr(data-label);
            flex: 0 0 42%;
            color: #6b7280;
            font-size: 0.74rem;
            font-weight: bold;
            text-align: right;
            line-height: 1.8;
            order: 2;
        }

        .automation-dashboard table td > * {
            max-width: 58%;
            width: 58%;
            text-align: right !important;
            margin-left: 0 !important;
            margin-right: auto !important;
            order: 1;
        }

        .progress-cell {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start !important;
            text-align: right !important;
        }

        .progress-cell .ui.progress {
            width: 100%;
            margin: 0 !important;
        }

        .progress-cell small {
            font-size: 0.68rem;
            max-width: 100%;
            width: 100%;
            margin-top: 0.35rem;
            text-align: right !important;
            white-space: normal;
        }

        .run-actions {
            width: 58% !important;
            max-width: 58% !important;
            justify-content: flex-start;
            direction: rtl;
        }

        .run-actions .button,
        .run-actions a.button,
        .run-actions button.button {
            margin: 0 !important;
            font-size: 0.72rem;
        }

        .run-id {
            font-size: 0.8rem;
            text-align: right !important;
            display: inline-block;
            width: 100%;
        }

        .ui.label {
            font-size: 0.68rem;
        }
    }

    @media only screen and (max-width: 420px) {
        .automation-dashboard table td::before {
            flex-basis: 38%;
            font-size: 0.68rem;
        }

        .automation-dashboard table td > * {
            max-width: 62%;
            width: 62%;
        }

        .run-actions {
            width: 62% !important;
            max-width: 62% !important;
        }

        .automation-dashboard .ui.statistic > .value {
            font-size: 1.35rem !important;
        }

        .automation-dashboard .ui.statistic > .label {
            font-size: 0.72rem !important;
        }
    }
</style>
