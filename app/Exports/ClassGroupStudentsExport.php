<?php

namespace App\Exports;

use App\Models\ClassGroup;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassGroupStudentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;

    /**
     * @param  Collection<int, mixed>  $histories
     */
    public function __construct(
        private readonly ClassGroup $classGroup,
        private readonly ?Semester $semester,
        private readonly Collection $histories
    ) {}

    /**
     * Data utama yang akan diekspor.
     *
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        return $this->histories;
    }

    /**
     * Judul kolom Excel.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NIS',
            'NISN',
            'Nomor Registrasi',
            'Tahun Ajaran',
            'Semester',
            'Rombel',
            'Tingkat',
            'Ruangan',
            'Status Penempatan',
            'Keterangan',
        ];
    }

    /**
     * Mengubah setiap data riwayat kelas menjadi baris Excel.
     *
     * @param  mixed  $history
     * @return array<int, mixed>
     */
    public function map($history): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $history->student?->person?->full_name ?? '-',
            $history->student?->student_number ?? '-',
            $history->student?->nisn ?? '-',
            $history->student?->registration_number ?? '-',
            $history->academicYear?->name ?? $this->classGroup->academicYear?->name ?? '-',
            $history->semester?->name ?? $this->semester?->name ?? '-',
            $this->classGroup->name,
            $this->classGroup->gradeLevel?->name ?? '-',
            $this->classGroup->room?->name ?? '-',
            $history->status,
            $history->notes ?? '-',
        ];
    }

    /**
     * Nama sheet Excel.
     */
    public function title(): string
    {
        return 'Daftar Siswa';
    }

    /**
     * Styling sederhana agar header tebal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
