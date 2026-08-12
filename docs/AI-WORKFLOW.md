# AI Workflow — SIM Madrasah

Dokumen ini menjelaskan cara kerja dengan AI agar project tidak kacau.

---

## 1. Prinsip Utama

AI boleh membantu, tetapi project harus tetap punya dokumentasi internal.

Gunakan aturan:

```txt
Code = sumber kebenaran utama.
Git = histori perubahan.
Docs = peta project.
AI History = arsip percakapan mentah.
```

---

## 2. Alur Setiap Sesi AI

Sebelum meminta AI membuat kode, minta AI membaca:

```txt
README.md
AI-INSTRUCTIONS.md
docs/AI-HANDOFF.md
docs/PROGRESS.md
docs/NEXT-STEPS.md
docs/ARCHITECTURE.md
docs/DATABASE.md
docs/RBAC.md
```

Lalu beri instruksi:

```txt
Kerjakan satu tahap kecil saja. Jangan melompat modul. Setelah selesai, berikan update dokumentasi.
```

---

## 3. Format Prompt Lanjutan

```txt
Anda melanjutkan project SIM Madrasah berbasis Laravel.

Baca dulu:
- README.md
- AI-INSTRUCTIONS.md
- docs/AI-HANDOFF.md
- docs/PROGRESS.md
- docs/NEXT-STEPS.md
- docs/ARCHITECTURE.md
- docs/DATABASE.md
- docs/RBAC.md

Konteks:
- Saya pemula.
- Jelaskan konsep dulu.
- Jangan langsung banyak kode.
- Kerjakan satu tahap kecil.
- Setelah selesai, update dokumentasi.

Tugas sekarang:
[TULIS TAHAP DI SINI]
```

---

## 4. Format Jawaban yang Diminta dari AI

Minta AI menjawab dengan pola:

```txt
1. Tujuan tahap
2. File yang diubah
3. Penjelasan konsep
4. Kode per file
5. Perintah test
6. Cara uji manual
7. Update dokumentasi
8. Commit message
```

---

## 5. File yang Harus Diupdate Setelah Tahap

Minimal:

- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Kondisional:

- `docs/DATABASE.md`
- `docs/RBAC.md`
- `docs/MODULES.md`
- `docs/TESTING.md`
- `docs/DECISIONS.md`

---

## 6. Kapan Membuka AI History

Buka `SIM-MADRASAH-AI-HISTORY.md` hanya jika:

- ada alasan keputusan lama yang belum tercatat di `docs/DECISIONS.md`,
- AI baru bingung urutan tahap,
- ada konflik antara kode dan dokumentasi,
- ingin mencari instruksi lama yang spesifik.

Jangan membaca file history penuh untuk setiap sesi.

---

## 7. Template Update Handoff

Setelah tahap selesai, update bagian ini di `docs/AI-HANDOFF.md`:

```md
## Status Terakhir Project

Tahap terakhir selesai:
- Nomor tahap:
- Nama tahap:
- Commit:
- File utama berubah:
- Test:
- Catatan penting:

## Tugas Berikutnya

Tahap berikutnya:
- Tujuan:
- File kemungkinan berubah:
- Risiko:
```
