<?php

namespace App\Exports;

use App\Models\RepairTicket;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RepairTicketsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    private int $rowNumber = 0;

    /**
     * @param  Collection<int, RepairTicket>  $tickets
     */
    public function __construct(public Collection $tickets) {}

    /**
     * @return Collection<int, RepairTicket>
     */
    public function collection(): Collection
    {
        return $this->tickets;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Kode Tiket',
            'Tanggal Lapor',
            'Pelapor',
            'Kode Aset',
            'Nama Aset',
            'Kerusakan',
            'Status',
            'Ranking SAW',
            'Skor SAW',
            'Biaya Perkiraan',
            'Biaya Riil',
            'Catatan Penyelesaian',
        ];
    }

    /**
     * @param  RepairTicket  $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        $this->rowNumber++;

        $sawRank = isset($row->saw_rank) && $row->saw_rank !== null ? '#'.$row->saw_rank : '-';
        $sawScore = isset($row->saw_score) && $row->saw_score !== null ? number_format((float) $row->saw_score, 4, ',', '.') : '-';
        $estimatedCost = $row->estimated_cost !== null ? (float) $row->estimated_cost : '-';
        $actualCost = $row->actual_cost !== null ? (float) $row->actual_cost : '-';

        return [
            $this->rowNumber,
            $row->ticket_number,
            $row->created_at?->format('d/m/Y H:i') ?? '-',
            $row->reporter?->name ?? '-',
            $row->asset?->code ?? '-',
            $row->asset?->name ?? '-',
            $row->damage_description,
            $row->status->label(),
            $sawRank,
            $sawScore,
            $estimatedCost,
            $actualCost,
            $row->completion_notes ?? '-',
        ];
    }

    /**
     * @return array<mixed, mixed>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
