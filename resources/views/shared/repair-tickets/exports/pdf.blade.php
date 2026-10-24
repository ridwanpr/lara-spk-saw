<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tiket Perbaikan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #212529;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0 0 4px 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 8.5pt;
            color: #555;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #999;
            padding: 5px 4px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f0f2f5;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .badge-secondary { background: #e2e8f0; color: #334155; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 15px;
            font-size: 7.5pt;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Tiket Perbaikan Aset</h2>
        <p>Sistem Pendukung Keputusan Penentuan Prioritas Perbaikan Aset (Metode SAW)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Status Filter:</strong></td>
            <td style="width: 35%;">{{ $filters['status_label'] ?? 'Semua Status' }}</td>
            <td style="width: 15%;"><strong>Urutan Data:</strong></td>
            <td style="width: 35%;">{{ $filters['sort_label'] ?? 'Tanggal Terbaru' }}</td>
        </tr>
        <tr>
            <td><strong>Rentang Tanggal:</strong></td>
            <td>{{ $filters['date_range'] ?? 'Semua Tanggal' }}</td>
            <td><strong>Dicetak Pada:</strong></td>
            <td>{{ now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 11%;">Kode Tiket</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 12%;">Pelapor</th>
                <th style="width: 14%;">Aset</th>
                <th style="width: 18%;">Deskripsi Kerusakan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 9%;">Prioritas SAW</th>
                <th style="width: 12%;">Biaya (Est / Riil)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $index => $ticket)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $ticket->ticket_number }}</strong></td>
                    <td class="text-center">{{ $ticket->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $ticket->reporter?->name ?? '-' }}</td>
                    <td>
                        <strong>{{ $ticket->asset?->code ?? '-' }}</strong><br>
                        <span style="color: #555;">{{ $ticket->asset?->name ?? '-' }}</span>
                    </td>
                    <td>{{ $ticket->damage_description }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $ticket->status->color() }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if(isset($ticket->saw_rank) && $ticket->saw_rank !== null)
                            <strong>Rank #{{ $ticket->saw_rank }}</strong><br>
                            <span style="font-size: 7.5pt; color: #555;">Skor: {{ number_format((float) $ticket->saw_score, 4, ',', '.') }}</span>
                        @else
                            <span style="color: #888;">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($ticket->estimated_cost !== null)
                            <span style="color: #666; font-size: 7.5pt;">Est:</span> Rp {{ number_format((float) $ticket->estimated_cost, 0, ',', '.') }}<br>
                        @endif
                        @if($ticket->actual_cost !== null)
                            <span style="color: #166534; font-size: 7.5pt; font-weight: bold;">Riil:</span> Rp {{ number_format((float) $ticket->actual_cost, 0, ',', '.') }}
                        @endif
                        @if($ticket->estimated_cost === null && $ticket->actual_cost === null)
                            <span style="color: #888;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #777;">
                        Tidak ada data tiket perbaikan yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis dari Sistem SPK SAW Aset &bull; Total data: {{ $tickets->count() }}
    </div>
</body>
</html>
