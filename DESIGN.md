# DESIGN.md — Math Hero

## Subject / audience / primary job

**Subject:** Petualangan matematika SD kelas 4-6, bertema petualangan alam (eksplorasi, hutan, peta, penemuan).
**Audience:** Anak SD umur 9-12 (primary), guru/ortu (secondary, via class report).
**Primary job of the main screen (`play`):** anak fokus menjawab SATU soal dalam 30 detik. Segala sesuatu yang mengganggu fokus itu adalah bug.

## Voice

**Berani, ramah, jelas.** Bahasa Indonesianya anak SD: "Gas!", "Mantap!", "Yuk lanjut!"
Bukan bahasa formal, bukan juga slang dewasa. Microcopy selalu menjelaskan apa selanjutnya,
bukan apa yang salah. Tema: penjelajah hutan ("jelajah", "jejak", "bintang").

## Design system: ESTABLISH (new project, no existing tokens)

## Token spine

### Color roles (semantic, nama fungsional — bukan nama warna)

| Token | Value | Untuk |
|-------|-------|-------|
| `--canvas` | `#F4F1E4` | Background utama — hangat, kertas alam, bukan putih steril |
| `--surface` | `#FFFFFF` | Card, soal, panel |
| `--surface-raised` | `#FCFAF1` | Card di atas card (review section) |
| `--ink` | `#22301F` | Teks utama — hijau-pekat alam, bukan hitam |
| `--ink-muted` | `#6B7A5E` | Teks sekunder, timer, hint |
| `--hero` | `#2E7D32` | Aksi utama ("Gas", "Lanjut") — hijau petualangan |
| `--hero-ink` | `#FFFFFF` | Teks di atas hero |
| `--success` | `#2E7D32` | Jawaban benar, unlock (sama dengan hero) |
| `--danger` | `#C62828` | Jawaban salah, warning |
| `--focus` | `#1565C0` | Focus ring, link |
| `--locked` | `#B5AE96` | Level terkunci, disabled |
| `--line` | `rgba(34,48,31,0.12)` | Border hairline |
| `--gold` | `#F9A825` | Bintang, achievement, 100% |
| `--accent-earth` | `#8D6E63` | Aksen tanah/kayu ( dekorasi peta, badge ) |

Warna berani (hero/success/danger) **hanya** untuk feedback & aksi utama. Body tetap hangat-netral. Tidak ada gradient ungu-biru. Tidak ada glassmorphism.

### Type scale

Typeface: **Baloo 2** (Google Fonts, rounded display) untuk heading + soal + angka; **Inter** untuk body/label. Baloo 2 bulat & playful = cocok anak SD, dan itu BUKAN default Inter-di-mana-mana.

| Token | Size / weight | Untuk |
|-------|---------------|-------|
| `--t-soal` | 28px / 600 Baloo 2 | Teks soal di play — BESAR, readable |
| `--t-h1` | 24px / 700 Baloo 2 | Judul layar |
| `--t-h2` | 18px / 700 Baloo 2 | Section heading |
| `--t-body` | 16px / 400 Inter | Body, opsi jawaban |
| `--t-label` | 13px / 600 Inter, uppercase | Category label (satu-satunya tempat caps) |
| `--t-num` | Baloo 2, tabular | Skor, timer, level number |

### Spacing scale (4px base)

`--s1: 4px` `--s2: 8px` `--s3: 12px` `--s4: 16px` `--s5: 24px` `--s6: 32px` `--s7: 48px`

Rhythm: rapat dalam grup (s2-s3), generous antar grup (s6-s7).

### Radius scale

`--r-sm: 8px` (input) · `--r-md: 16px` (option card) · `--r-lg: 24px` (panel/soal card) · `--r-pill: 999px` (timer chip, badge)

### Shadow scale

`--sh-1: 0 1px 2px rgba(42,30,15,0.08)` (card) · `--sh-2: 0 4px 12px rgba(42,30,15,0.12)` (soal card fokus) · `--sh-glow: 0 0 0 4px rgba(245,168,37,0.30)` (bintang/achievement only — bukan untuk semua hover)

## Signature bet

**Timer ring + peta petualangan yang "bernapas".**

Di `play`: soal tampil dalam card putih besar di atas canvas kertas alam. Di pojok kanan atas card ada **cincin timer melingkar** (SVG stroke-dashoffset, bukan angka doang) yang menyusut selama 30 detik. Warna cincin: `--focus` di 30-10s, `--gold` 10-5s, `--danger` 5-0s.

Di `map`: level-level disusun **seperti jalur kompas di atas kertas perkamen**, bukan grid kotak. Jalur berkelok (SVG path) menghubungkan LevelNode; node yang perfect bersinar `--gold`. Tiap kelas = satu peta.

Kenapa ini signature, dan kenapa bukan slop: timer adalah **sumber tekanan utama** game ini — membuatnya melingkar + berubah warna = anak ngerasakan detiknya tanpa baca angka. Dan peta jalur adalah janji tema "petualangan" yang ditepati secara visual, bukan kata "petualangan" + grid biasa. Keduanya fungsional (tekanan + navigasi), bukan dekorasi. Dan itu bentuk yang **gak akan kelihatan bener di kompetitor quiz generik** — mereka pakai bar tipis, angka kecil, dan list bernomor.

Motion budget: cincin = animasi 1 fps tick (bukan smooth), karena "tick" itu feedback. Opsi jawaban: scale 1.02 + border color saat hover, 150ms ease-out. Tidak ada bounce/elastic. `prefers-reduced-motion`: cincin jadi angka statis.

## Responsive contract — "phone dulu, scale-up"

Anak SD mainnya di HP ortu. Phone = layout dasar yang harus sempurna; tablet/laptop = bonus yang harus gak kelihatan terlantar.

**Breakpoints (mobile-first, min-width):**
- `<640px` — HP: 1 kolom, padding 16px, target sentuh minimal 44px (jarak tempuh ibu jari)
- `640-1024px` — tablet: container cap 640px, list bisa 2 kolom (roster, level progress), bar chart tetap 1 kolom
- `>1024px` — laptop: container cap 880-1080px tergantung layar, 2-3 kolom untuk roster/report, sidebar detail untuk map

**Aturan wajib:**
- `100dvh` BUKAN `100vh` (viewport unit lama mengabaikan chrome browser mobile — tombol "Gas" bisa ketutup address bar saat scroll)
- `env(safe-area-inset-bottom)` padding bawah (iPhone notch/Home indicator)
- `touch-action: manipulation` pada semua target sentuh (hapus 300ms double-tap-zoom delay)
- `user-select: none` pada tombol (anak nge-klik-doang sering mentriger seleksi teks)
- Fluid type: `clamp()` untuk heading/soal — soal 28px di HP kecil, 32px di besar, bukan fix 28
- Semua konten `max-width` capped di >640px; tidak ada yang full-width stretch di desktop kecuali background
- `prefers-reduced-motion`: cincin jadi angka statis (sudah ada), tambah disable scale hover

## Knobs

- `DESIGN_VARIANCE`: 6 (playful tapi terkendali; ini game anak, boleh sedikit lebih berani dari dashboard, tapi jangan sirkus)
- `VISUAL_DENSITY`: 3 (editorial — anak SD butuh whitespace besar; jangan sesak)
- `MOTION_INTENSITY`: 3 (feedback functional doang; honor `prefers-reduced-motion`)

## Banned (explicit)

- Gradient ungu→biru/cyan. Gradient text. Glassmorphism + neon glow.
- Inter untuk segalanya (Baloo 2 untuk display).
- Card di dalam card untuk "nambah kedalaman".
- Emoji sebagai feature icon. Lucide/Heroicons SVG.
- "Lorem ipsum" atau "John Doe". Soal pakai nama Indonesia (Budi, Sari, Pak Budi) + konteks Indonesia (gelas, aquarium, pasar, kelas).
- ALL-CAPS heading. Cuma `--t-label` (13px) yang caps.
- Radius sama untuk semuanya (lihat radius scale — sengaja bervariasi).
- 3-6 card identik sejajar.

## Core components (3-4, punya opinion)

1. **QuestionCard** — card putih, radius lg, shadow 2, timer ring di pojok kanan atas, soal `--t-soal`, 4 OptionCard di bawah.
2. **OptionCard** — surface, radius md, border 2px line, hover scale 1.02 + border focus; state: default / selected / correct / wrong / disabled.
3. **LevelNode** — lingkaran di map (jalur SVG), isi angka level Baloo 2; state: locked (gray, gembok) / available (canvas, border) / perfect (gold glow, bintang).
4. **StatChip** — pill kecil untuk skor/waktu di results & leaderboard (`--t-num`).
