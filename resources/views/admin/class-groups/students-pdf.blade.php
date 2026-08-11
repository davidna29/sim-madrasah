<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Siswa Per Kelas</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 11px;
            margin: 24px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #166534;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .school {
            font-size: 18px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 4px;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin-bottom: 16px;
            font-size: 11px;
        }

        .meta td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 120px;
            color: #6b7280;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #9ca3af;
            padding: 7px;
            text-align: left;
            font-weight: bold;
        }

        table.data td {
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
        }

        .signature {
            width: 100%;
            margin-top: 36px;
            font-size: 11px;
        }

        .signature td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        .name-line {
            margin-top: 56px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school">SIM Madrasah</div>
        <div class="title">Daftar Siswa Per Kelas</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $classGroup->academicYear?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td>: {{ $semester?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Rombel</td>
            <td>: {{ $classGroup->name }}</td>
        </tr>
        <tr>
            <td class="label">Tingkat</td>
            <td>: {{ $classGroup->gradeLevel?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ruangan</td>
            <td>: {{ $classGroup->room?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Wali Kelas</td>
            <td>: {{ $classGroup->homeroomTeacher?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Siswa</td>
            <td>: {{ $histories->count() }} siswa</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="center" style="width: 32px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 90px;">NIS</th>
                <th style="width: 100px;">NISN</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 120px;">Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($histories as $history)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $history->student?->person?->full_name ?? '-' }}</td>
                    <td>{{ $history->student?->student_number ?? '-' }}</td>
                    <td>{{ $history->student?->nisn ?? '-' }}</td>
                    <td>{{ $history->status }}</td>
                    <td>{{ $history->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        Belum ada siswa pada rombongan belajar ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah

                <div class="name-line">
                    ................................
                </div>
            </td>

            <td>
                Wali Kelas

                <div class="name-line">
                    {{ $classGroup->homeroomTeacher?->name ?? '................................' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem Informasi Manajemen Madrasah.
    </div>
</body>
</html>
