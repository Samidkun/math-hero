import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { type PageProps, type ProgressSummary } from '@/types';

interface HomeProps extends PageProps {
  progressSummary: ProgressSummary | null;
}

export default function Home({ auth, progressSummary }: HomeProps) {
  const [nickname, setNickname] = useState(auth.player?.nickname || '');
  const [grade, setGrade] = useState<number>(4);
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const handleStart = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nickname.trim()) return;

    setSubmitting(true);
    setErrorMsg('');

    try {
      const res = await fetch('/api/players', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          nickname: nickname.trim(),
          grade,
        }),
      });

      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || 'Gagal memulai petualangan');
      }

      router.visit('/map');
    } catch (err: unknown) {
      if (err instanceof Error) {
        setErrorMsg(err.message);
      } else {
        setErrorMsg('Terjadi kesalahan.');
      }
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-5xl items-center justify-between gap-4 p-4 sm:px-8">
        <div className="flex items-center gap-3 font-display text-xl font-bold text-hero">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z" />
          </svg>
          <span>Math Hero</span>
        </div>
        <nav className="flex items-center gap-4 text-sm font-semibold">
          <Link href="/join" className="text-focus hover:underline">
            Gabung Kelas
          </Link>
        </nav>
      </header>

      {/* Main Panel */}
      <main className="mx-auto max-w-xl px-4 py-8 sm:py-12">
        <div className="rounded-3xl bg-surface p-6 shadow-md sm:p-10">
          <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">
            <span>Petualangan Matematika</span>
            <span>·</span>
            <span>Kurikulum Merdeka</span>
          </div>

          <h1 className="mt-3 font-display text-3xl font-bold leading-tight sm:text-4xl text-ink">
            {auth.player ? `Halo, ${auth.player.nickname}!` : 'Siap Menjelajah Dunia Angka?'}
          </h1>
          <p className="mt-2 text-sm sm:text-base text-ink-muted">
            Taklukkan 10 level tantangan di setiap tingkatan kelas dan jadilah juara matematika!
          </p>

          {/* Returning Player Quick Progress */}
          {auth.player && progressSummary ? (
            <div className="mt-6 rounded-2xl bg-surface-raised border border-line p-5">
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">Progres Petualangan</span>
                <span className="font-display text-sm font-bold text-hero">
                  Level {progressSummary.next_level} / 10 Terbuka
                </span>
              </div>
              <div className="mt-3 h-3 w-full overflow-hidden rounded-full bg-canvas border border-line">
                <div
                  className="h-full bg-hero rounded-full transition-all duration-500"
                  style={{ width: `${(progressSummary.unlocked_levels / 10) * 100}%` }}
                />
              </div>
              <div className="mt-4 flex flex-wrap gap-2">
                <span className="inline-flex items-center gap-1.5 rounded-full bg-surface px-3 py-1 text-xs font-bold font-display border border-line text-gold">
                  ★ {progressSummary.total_stars} Bintang
                </span>
                <span className="inline-flex items-center gap-1.5 rounded-full bg-surface px-3 py-1 text-xs font-bold font-display border border-line text-hero">
                  ✓ {progressSummary.perfect_levels} Level Sempurna
                </span>
              </div>

              <Link
                href="/map"
                className="mt-6 flex w-full items-center justify-center rounded-2xl bg-hero py-3.5 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98]"
              >
                Lanjut Bermain di Level {progressSummary.next_level} →
              </Link>
            </div>
          ) : (
            /* New Player Form */
            <form onSubmit={handleStart} className="mt-6">
              {errorMsg && (
                <div className="mb-4 rounded-xl bg-danger/10 p-3 text-sm font-semibold text-danger border border-danger/20">
                  {errorMsg}
                </div>
              )}

              <div>
                <label className="block text-sm font-semibold text-ink">
                  Nama Panggilanmu
                </label>
                <input
                  type="text"
                  required
                  maxLength={32}
                  value={nickname}
                  onChange={(e) => setNickname(e.target.value)}
                  placeholder="Contoh: Budi, Sari, Fauzan"
                  className="mt-1.5 w-full rounded-xl border-2 border-line bg-surface px-4 py-3 text-base text-ink outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20"
                />
                <p className="mt-1 text-xs text-ink-muted">
                  Bisa langsung main tanpa kode kelas!
                </p>
              </div>

              {/* Grade Selector */}
              <div className="mt-5">
                <label className="block text-sm font-semibold text-ink">
                  Pilih Tingkat Kelas
                </label>
                <div className="mt-2 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                  {[4, 5, 6].map((g) => (
                    <button
                      key={g}
                      type="button"
                      onClick={() => setGrade(g)}
                      className={`min-h-[44px] rounded-xl border-2 p-3 text-center transition ${
                        grade === g
                          ? 'border-hero bg-surface-raised font-bold text-hero shadow-sm'
                          : 'border-line bg-surface text-ink hover:border-focus hover:scale-[1.01]'
                      }`}
                    >
                      <div className="font-display text-base">Kelas {g}</div>
                      <div className="text-xs text-ink-muted">Fase {g === 4 ? 'B' : 'C'}</div>
                    </button>
                  ))}
                </div>
              </div>

              {/* CTA Button */}
              <button
                type="submit"
                disabled={submitting || !nickname.trim()}
                className="mt-6 w-full rounded-2xl bg-hero py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-locked"
              >
                {submitting ? 'Menyiapkan...' : 'Gas, Mulai Petualangan! 🚀'}
              </button>

              <div className="mt-6 border-t border-line pt-4 text-center">
                <p className="text-xs text-ink-muted">Punya kode dari guru atau orang tua?</p>
                <Link href="/join" className="mt-1 inline-block text-sm font-bold text-focus hover:underline">
                  Gabung ke Kelas yang Sudah Ada →
                </Link>
              </div>
            </form>
          )}
        </div>
      </main>
    </div>
  );
}
