import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { type PageProps, type LevelItem } from '@/types';

interface MapProps extends PageProps {
  grade: number;
  levels: LevelItem[];
  stats: {
    total_stars: number;
    open_levels: number;
  };
}

// 10 node coordinates on 900x620 viewBox
const NODE_COORDS = [
  { x: 95, y: 500 },
  { x: 205, y: 380 },
  { x: 320, y: 490 },
  { x: 430, y: 370 },
  { x: 540, y: 480 },
  { x: 655, y: 360 },
  { x: 765, y: 455 },
  { x: 810, y: 290 },
  { x: 680, y: 160 },
  { x: 530, y: 90 },
];

export default function Map({ auth, grade, levels, stats }: MapProps) {
  // Select first unlocked and non-perfect level by default, or level 1
  const initialSelected = levels.find((l) => l.is_unlocked && !l.is_perfect) || levels[0];
  const [selected, setSelected] = useState<LevelItem>(initialSelected);
  const [starting, setStarting] = useState(false);

  // Build Catmull-Rom cubic bezier SVG path
  const buildTrail = (pts: { x: number; y: number }[]) => {
    if (pts.length < 2) return '';
    let d = `M${pts[0].x} ${pts[0].y}`;
    for (let i = 0; i < pts.length - 1; i++) {
      const p0 = pts[Math.max(0, i - 1)];
      const p1 = pts[i];
      const p2 = pts[i + 1];
      const p3 = pts[Math.min(pts.length - 1, i + 2)];
      const c1x = p1.x + (p2.x - p0.x) / 6;
      const c1y = p1.y + (p2.y - p0.y) / 6;
      const c2x = p2.x - (p3.x - p1.x) / 6;
      const c2y = p2.y - (p3.y - p1.y) / 6;
      d += ` C${c1x.toFixed(1)} ${c1y.toFixed(1)} ${c2x.toFixed(1)} ${c2y.toFixed(1)} ${p2.x} ${p2.y}`;
    }
    return d;
  };

  const handleStartLevel = async () => {
    if (!selected.is_unlocked || starting || !auth.player) return;

    setStarting(true);
    try {
      const res = await fetch('/api/sessions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          player_id: auth.player.id,
          level_id: selected.id,
        }),
      });

      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || 'Gagal memulai level');
      }

      const data = await res.json();
      router.visit(`/play/${data.session_id}`);
    } catch (err: unknown) {
      alert(err instanceof Error ? err.message : 'Terjadi kesalahan.');
      setStarting(false);
    }
  };

  const firstLockedIdx = levels.findIndex((l) => !l.is_unlocked);
  const openEnd = firstLockedIdx === -1 ? levels.length : firstLockedIdx + 1;
  const openPts = NODE_COORDS.slice(0, openEnd);
  const lockedPts = NODE_COORDS.slice(Math.max(0, openEnd - 1));

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-5xl items-center justify-between gap-4 p-4 sm:px-8">
        <Link href="/" className="flex items-center gap-3 font-display text-xl font-bold text-hero hover:opacity-90">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <circle cx="12" cy="12" r="9" />
            <path d="M15.5 8.5l-2 5.5-5.5 2 2-5.5z" />
          </svg>
          <span>Math Hero</span>
        </Link>
        <div className="flex items-center gap-4 text-sm font-semibold">
          <span className="text-ink-muted">Pemain: <b className="text-ink">{auth.player?.nickname}</b></span>
          <Link href="/" className="text-focus hover:underline">
            Beranda
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6 sm:py-8">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
              Kelas {grade} · Peta Petualangan
            </div>
            <h1 className="mt-1 font-display text-3xl font-bold sm:text-4xl">
              Peta Level {auth.player?.nickname}
            </h1>
            <p className="mt-1 text-sm text-ink-muted max-w-xl">
              Setiap level hanya terbuka kalau level sebelumnya selesai dengan nilai 100%. Selesaikan semua tantangan!
            </p>
          </div>

          <div className="flex gap-2 flex-wrap">
            <span className="inline-flex items-center gap-1.5 rounded-full bg-surface px-4 py-2 text-sm font-bold font-display border border-line shadow-sm text-gold">
              ★ {stats.total_stars} Bintang
            </span>
            <span className="inline-flex items-center gap-1.5 rounded-full bg-surface px-4 py-2 text-sm font-bold font-display border border-line shadow-sm text-hero">
              ✓ {stats.open_levels} / 10 Terbuka
            </span>
          </div>
        </div>

        {/* Map Layout */}
        <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
          {/* SVG Parchment Map */}
          <div className="relative rounded-3xl bg-surface p-4 shadow-md overflow-hidden border border-line">
            <svg viewBox="0 0 900 620" className="w-full h-auto block select-none">
              {/* Border decoration */}
              <rect
                x="10"
                y="10"
                width="880"
                height="600"
                rx="18"
                fill="none"
                stroke="#8D6E63"
                strokeWidth="2"
                strokeDasharray="3 8"
                opacity="0.45"
              />

              {/* Compass Rose */}
              <g transform="translate(842,70)" opacity="0.45" aria-hidden="true">
                <circle r="26" fill="none" stroke="#8D6E63" strokeWidth="2" />
                <path d="M0 -22 L5 0 L0 22 L-5 0 Z" fill="#8D6E63" />
                <path d="M-22 0 L0 -5 L22 0 L0 5 Z" fill="#8D6E63" opacity="0.45" />
                <text y="-30" textAnchor="middle" className="font-display font-bold text-xs" fill="#8D6E63">
                  U
                </text>
              </g>

              {/* Trails */}
              {openPts.length > 1 && (
                <path d={buildTrail(openPts)} fill="none" stroke="#2E7D32" strokeWidth="6" strokeLinecap="round" opacity="0.5" />
              )}
              {lockedPts.length > 1 && (
                <path
                  d={buildTrail(lockedPts)}
                  fill="none"
                  stroke="#8D6E63"
                  strokeWidth="5"
                  strokeDasharray="4 12"
                  strokeLinecap="round"
                  opacity="0.4"
                />
              )}

              {/* Level Nodes */}
              {levels.map((lvl, idx) => {
                const coord = NODE_COORDS[idx] || { x: 50 + idx * 80, y: 300 };
                const isSelected = selected.id === lvl.id;
                const isLocked = !lvl.is_unlocked;
                const isPerfect = lvl.is_perfect;

                return (
                  <g
                    key={lvl.id}
                    transform={`translate(${coord.x}, ${coord.y})`}
                    onClick={() => setSelected(lvl)}
                    className="cursor-pointer transition-transform duration-200"
                    style={{ outline: 'none' }}
                  >
                    {/* Hit box */}
                    <circle r="44" fill="transparent" />

                    {/* Outer glow ring for selected or perfect */}
                    {isSelected && (
                      <circle r="42" fill="none" stroke="#1565C0" strokeWidth="3" strokeDasharray="4 4" />
                    )}
                    {isPerfect && !isSelected && (
                      <circle r="42" fill="none" stroke="#F9A825" strokeWidth="2" strokeDasharray="3 5" opacity="0.8" />
                    )}

                    {/* Main Node Body */}
                    <circle
                      r="34"
                      fill={isLocked ? '#B5AE96' : isPerfect ? '#F9A825' : '#FFFFFF'}
                      stroke={isLocked ? 'rgba(34,48,31,0.2)' : isPerfect ? '#C77800' : isSelected ? '#1565C0' : '#2E7D32'}
                      strokeWidth={isSelected ? '4' : '3.5'}
                      className="shadow-sm"
                    />

                    {/* Number */}
                    <text
                      textAnchor="middle"
                      dominantBaseline="central"
                      className="font-display font-bold text-2xl select-none"
                      fill={isLocked ? '#5C5A4C' : isPerfect ? '#4A3400' : isSelected ? '#1565C0' : '#22301F'}
                    >
                      {lvl.number}
                    </text>

                    {/* Status Badge Icon */}
                    {isLocked ? (
                      <g transform="translate(0, 1)">
                        <rect x="-8" y="-4" width="16" height="11" rx="2" fill="#5C5A4C" />
                        <path d="M-4 -4 v-3 a4 4 0 0 1 8 0 v3" fill="none" stroke="#5C5A4C" strokeWidth="2.5" />
                      </g>
                    ) : isPerfect ? (
                      <g transform="translate(26, -26) scale(1.1)">
                        <path
                          d="M0 -8 l2.4 5.2 5.7.7-4.1 3.9 1.1 5.7-5.1-2.8-5.1 2.8 1.1-5.7-4.1-3.9 5.7-.7z"
                          fill="#F9A825"
                          stroke="#C77800"
                          strokeWidth="1.2"
                        />
                      </g>
                    ) : (
                      <g transform="translate(26, -26)">
                        <circle r="10" fill="#2E7D32" />
                        <path d="M-3 -4 l7 4 -7 4z" fill="#FFFFFF" />
                      </g>
                    )}

                    {/* Short title below node */}
                    <text
                      y="48"
                      textAnchor="middle"
                      className="text-xs font-semibold select-none"
                      fill={isLocked ? '#6B7A5E' : '#22301F'}
                    >
                      {lvl.title.length > 18 ? lvl.title.slice(0, 17) + '…' : lvl.title}
                    </text>
                  </g>
                );
              })}
            </svg>

            {/* Legend */}
            <div className="mt-4 flex flex-wrap gap-4 border-t border-line pt-3 text-xs text-ink-muted">
              <span className="inline-flex items-center gap-1.5">
                <span className="h-3 w-3 rounded-full border-2 border-hero bg-surface" />
                Terbuka, siap main
              </span>
              <span className="inline-flex items-center gap-1.5">
                <span className="h-3 w-3 rounded-full border-2 border-[#C77800] bg-gold" />
                Sempurna (Nilai 100%)
              </span>
              <span className="inline-flex items-center gap-1.5">
                <span className="h-3 w-3 rounded-full border border-line bg-locked" />
                Terkunci
              </span>
            </div>
          </div>

          {/* Level Detail Sheet */}
          <div className="rounded-3xl bg-surface p-6 shadow-md border border-line flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
                  Detail Level
                </span>
                <span
                  className={`rounded-full px-2.5 py-0.5 text-xs font-bold ${
                    !selected.is_unlocked
                      ? 'bg-locked/30 text-ink-muted'
                      : selected.is_perfect
                      ? 'bg-gold/20 text-[#A26000]'
                      : 'bg-hero/15 text-hero'
                  }`}
                >
                  {!selected.is_unlocked ? 'Terkunci' : selected.is_perfect ? 'Sempurna ★' : 'Terbuka'}
                </span>
              </div>

              <h2 className="mt-3 font-display text-2xl font-bold text-ink">
                Level {selected.number}: {selected.title}
              </h2>

              <p className="mt-2 text-sm text-ink-muted">
                {!selected.is_unlocked
                  ? 'Selesaikan level sebelumnya dengan nilai 100% untuk membuka tantangan ini.'
                  : selected.is_perfect
                  ? 'Kamu sudah menyelesaikan level ini dengan sempurna! Kamu bisa mengulanginya untuk rekor waktu tercepat.'
                  : 'Selesaikan 10 soal matematika dalam durasi 30 detik per soal. Dapatkan nilai 100% untuk membuka level berikutnya!'}
              </p>

              <div className="mt-4 space-y-2 rounded-2xl bg-surface-raised p-4 border border-line text-sm">
                <div className="flex justify-between">
                  <span className="text-ink-muted">Skor Terbaik:</span>
                  <span className="font-display font-bold text-ink">
                    {selected.best_score_pct}%
                  </span>
                </div>
                {selected.best_time_ms && (
                  <div className="flex justify-between">
                    <span className="text-ink-muted">Waktu Tercepat:</span>
                    <span className="font-display font-bold text-ink">
                      {(selected.best_time_ms / 1000).toFixed(1)} detik
                    </span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-ink-muted">Format:</span>
                  <span className="font-medium text-ink">10 Soal Pilihan Ganda</span>
                </div>
              </div>
            </div>

            <button
              type="button"
              disabled={!selected.is_unlocked || starting}
              onClick={handleStartLevel}
              className="mt-6 w-full rounded-2xl bg-hero py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-locked"
            >
              {starting
                ? 'Menyiapkan Soal...'
                : !selected.is_unlocked
                ? 'Level Terkunci 🔒'
                : selected.is_perfect
                ? `Main Ulang Level ${selected.number} ↺`
                : `Mulai Level ${selected.number} 🚀`}
            </button>
          </div>
        </div>
      </main>
    </div>
  );
}
