# Prompt Konteks untuk AI Baru

Gunakan prompt ini saat pindah ke AI/chat baru.

```txt
Anda melanjutkan project SIM Madrasah berbasis Laravel.

Sebelum menjawab atau memberi kode, baca dan pahami file berikut:
1. README.md
2. AI-INSTRUCTIONS.md
3. docs/AI-HANDOFF.md
4. docs/PROJECT-BRIEF.md
5. docs/PROGRESS.md
6. docs/NEXT-STEPS.md
7. docs/ARCHITECTURE.md
8. docs/DATABASE.md
9. docs/RBAC.md
10. docs/DECISIONS.md

SIM-MADRASAH-AI-HISTORY.md hanya digunakan sebagai arsip tambahan jika ada konteks yang belum jelas.

Aturan kerja:
- Saya pemula, jelaskan dengan bahasa sederhana.
- Jangan langsung membuat banyak kode sekaligus.
- Jelaskan konsep dulu.
- Ikuti tahap terakhir yang tertulis di docs/AI-HANDOFF.md.
- Jangan melompat ke tahap lain sebelum saya mengatakan LANJUT.
- Setelah menyelesaikan satu tahap, wajib berikan update dokumentasi untuk:
  - docs/AI-HANDOFF.md
  - docs/PROGRESS.md
  - docs/NEXT-STEPS.md
  - docs/CHANGELOG.md
  - docs/DATABASE.md jika ada perubahan database
  - docs/RBAC.md jika ada perubahan permission
  - docs/DECISIONS.md jika ada keputusan teknis penting

Mulai dengan menyebutkan tahap terakhir yang Anda pahami dan tahap berikutnya yang akan dikerjakan.
```
