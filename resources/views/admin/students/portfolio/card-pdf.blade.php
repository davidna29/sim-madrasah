<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Portofolio Siswa</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 24px;
        }

        .card {
            width: 100%;
            border: 2px solid #166534;
            border-radius: 12px;
            padding: 22px;
        }

        .header {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 14px;
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
            color: #374151;
        }

        .content {
            width: 100%;
        }

        .left {
            width: 65%;
            vertical-align: top;
        }

        .right {
            width: 35%;
            text-align: center;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .value {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        .qr {
            width: 150px;
            height: 150px;
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 8px;
        }

        .url {
            font-size: 9px;
            color: #6b7280;
            word-break: break-all;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
            font-size: 10px;
            color: #6b7280;
        }

        .note {
            margin-top: 10px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="school">
                SIM Madrasah
            </div>

            <div class="title">
                Kartu Portofolio Digital Siswa
            </div>
        </div>

        <table class="content">
            <tr>
                <td class="left">
                    <div class="label">Nama Lengkap</div>
                    <div class="value">
                        {{ $student->person?->full_name ?? '-' }}
                    </div>

                    <div class="label">NIS</div>
                    <div class="value">
                        {{ $student->student_number ?? '-' }}
                    </div>

                    <div class="label">NISN</div>
                    <div class="value">
                        {{ $student->nisn ?? '-' }}
                    </div>

                    <div class="label">Nomor Registrasi</div>
                    <div class="value">
                        {{ $student->registration_number ?? '-' }}
                    </div>

                    <div class="label">Tahun Ajaran Masuk</div>
                    <div class="value">
                        {{ $student->admissionAcademicYear?->name ?? '-' }}
                    </div>

                    <div class="label">Kelas Saat Ini</div>
                    <div class="value">
                        {{ $student->currentClassHistory?->classGroup?->name ?? 'Belum ada kelas aktif' }}
                    </div>

                    <div class="label">Semester</div>
                    <div class="value">
                        {{ $student->currentClassHistory?->semester?->name ?? '-' }}
                    </div>

                    <div>
                        <span class="badge">
                            Status: {{ $student->status }}
                        </span>
                    </div>
                </td>

                <td class="right">
                    <img
                        src="{{ $qrCodeDataUri }}"
                        class="qr"
                        alt="QR Code Portofolio"
                    >

                    <div class="label">
                        Scan untuk membuka portofolio
                    </div>

                    <div class="url">
                        {{ $portfolioUrl }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Kartu ini digunakan untuk mengakses portofolio digital siswa.
            Halaman portofolio tetap membutuhkan login dan hak akses yang sesuai.
        </div>

        <div class="note">
            Dicetak otomatis dari Sistem Informasi Manajemen Madrasah.
        </div>
    </div>
</body>
</html>
