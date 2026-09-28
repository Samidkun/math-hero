import { Link } from '@inertiajs/react';
import { type WrongReview } from '@/types';

interface ResultsProps {
  session_id: number;
  level: {
    id: number;
    number: number;
    title: string;
    grade: number;
  };
  score_pct: number;
  correct_count: number;
  total_time_ms: number;
  perfect: boolean;
  reviews: WrongReview[];
}

export default function Results({
  level,
  score_pct,
  correct_count,
  total_time_ms,
  perfect,
  reviews,
}: ResultsProps) {
  const durationSec = (total_time_ms / 1000).toFixed(1);

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-2xl items-center justify-between p-4 sm:px-6">
        <Link href="/map" className="flex items-center gap-2 font-display text-lg font-bold text-hero hover:opacity-90">
          <span>← Kembali ke Peta</span>
        </Link>
        <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
          Kelas {level.grade} · Level {level.number}
        </span>
      </header>

      <main className="mx-auto max-w-2xl px-4 py-4 sm:py-8">
        {/* Score Card */}
        <section className="rounded-3xl bg-surface p-6 sm:p-10 shadow-md border border-line text-center">
          <div className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
            Hasil Petualangan
          </div>

          <h1 className="mt-1 font-display text-2xl font-bold text-ink">
            {level.title}
          </h1>

          {/* Big Score Display */}
          <div className="mt-6 flex items-baseline justify-center gap-2">
            <span
              className={`font-display text-6xl sm:text-7xl font-bold tabular-nums ${
                perfect ? 'text-gold' : 'text-hero'
              }`}
            >
              {score_pct}%
            </span>
          </div>

          <p className="mt-2 text-sm text-ink-muted">
            {perfect
              ? 'Sempurna! Semua 10 soal terjawab dengan benar! 🏆'
              : `${correct_count} dari 10 soal benar. Belum 100%, coba lagi yuk!`}
          </p>

          {/* 3 Stars */}
          <div className="mt-4 flex justify-center gap-3">
            {[1, 2, 3].map((starIdx) => {
              const isOn = perfect || (score_pct >= 80 && starIdx <= 2) || (score_pct >= 50 && starIdx === 1);
              return (
                <svg
                  key={starIdx}
                  viewBox="0 0 24 24"
                  className={`h-10 w-10 sm:h-12 sm:w-12 transition-transform duration-300 ${
                    isOn ? 'fill-gold stroke-[#C77800] scale-110 drop-shadow-sm' : 'fill-line/40 stroke-line'
                  }`}
                  strokeWidth="1.5"
                >
                  <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3 6.2 20.4l1.1-6.5L2.6 9.3l6.5-.9z" />
                </svg>
              );
            })}
          </div>

          {/* Stat Chips */}
          <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 border-t border-line pt-6">
            <div className="rounded-2xl bg-surface-raised p-3 border border-line">
              <span className="block text-xs uppercase tracking-wider text-ink-muted">Benar</span>
              <span className="font-display text-lg font-bold text-success">
                {correct_count} / 10
              </span>
            </div>
            <div className="rounded-2xl bg-surface-raised p-3 border border-line">
              <span className="block text-xs uppercase tracking-wider text-ink-muted">Salah</span>
              <span className="font-display text-lg font-bold text-danger">
                {10 - correct_count} / 10
              </span>
            </div>
            <div className="col-span-2 sm:col-span-1 rounded-2xl bg-surface-raised p-3 border border-line">
              <span className="block text-xs uppercase tracking-wider text-ink-muted">Waktu</span>
              <span className="font-display text-lg font-bold text-ink">
                {durationSec}s
              </span>
            </div>
          </div>

          {/* Success Banner or Retry Warning */}
          {perfect ? (
            <div className="mt-6 rounded-2xl bg-gold/10 p-4 border border-gold/40 text-gold flex items-center justify-center gap-3">
              <span className="text-2xl">🌟</span>
              <span className="font-display text-base font-bold text-[#A26000]">
                Selamat! Level selanjutnya telah terbuka di peta petualangan!
              </span>
            </div>
          ) : (
            <div className="mt-6 rounded-2xl bg-danger/10 p-4 border border-danger/30 text-danger text-sm font-semibold">
              ⚠️ Level berikutnya baru akan terbuka kalau kamu berhasil mendapatkan nilai 100%!
            </div>
          )}

          {/* Action Buttons */}
          <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            {perfect ? (
              <Link
                href="/map"
                className="w-full sm:w-auto rounded-2xl bg-hero px-8 py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98]"
              >
                Lanjut ke Peta Petualangan 🗺️
              </Link>
            ) : (
              <>
                <Link
                  href="/map"
                  className="w-full sm:w-auto rounded-2xl bg-hero px-8 py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98]"
                >
                  Ulangi Level Ini ↺
                </Link>
                <Link
                  href="/map"
                  className="w-full sm:w-auto rounded-2xl bg-surface border-2 border-line px-6 py-4 font-display text-lg font-bold text-ink transition hover:border-focus"
                >
                  Kembali ke Peta
                </Link>
              </>
            )}
          </div>
        </section>

        {/* Wrong Answer Reviews (Evaluasi Jawaban) */}
        {!perfect && reviews.length > 0 && (
          <section className="mt-8">
            <div className="flex items-baseline justify-between px-2">
              <h2 className="font-display text-2xl font-bold text-ink">
                Ulasan Kesalahan & Evaluasi
              </h2>
              <span className="text-xs text-ink-muted font-semibold">
                {reviews.length} Soal Perlu Dipelajari
              </span>
            </div>

            <div className="mt-4 space-y-4">
              {reviews.map((rev, idx) => (
                <div
                  key={idx}
                  className="rounded-2xl bg-surface p-5 shadow-sm border border-line"
                >
                  <div className="flex items-start gap-3">
                    <span className="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-canvas font-display text-sm font-bold text-ink">
                      {rev.index + 1}
                    </span>
                    <p className="font-display text-lg font-bold leading-snug text-ink">
                      {rev.stem}
                    </p>
                  </div>

                  <div className="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 text-sm">
                    <div className="rounded-xl border-l-4 border-danger bg-danger/5 p-3">
                      <span className="block text-xs font-semibold text-danger">Jawabanmu:</span>
                      <span className="font-display text-base font-bold text-ink">
                        {rev.chosen_text || 'Waktu habis'}
                      </span>
                    </div>
                    <div className="rounded-xl border-l-4 border-success bg-success/5 p-3">
                      <span className="block text-xs font-semibold text-success">Jawaban Benar:</span>
                      <span className="font-display text-base font-bold text-ink">
                        {rev.correct_text}
                      </span>
                    </div>
                  </div>

                  {rev.misconception && (
                    <div className="mt-3 rounded-xl bg-accent-earth/10 p-3 border-l-4 border-accent-earth text-xs">
                      <span className="block font-bold uppercase tracking-wider text-accent-earth">
                        Diagnosa Kesalahan: {rev.misconception}
                      </span>
                      <p className="mt-0.5 text-ink-muted">
                        Jangan terburu-buru menghitung. Pastikan kembali langkah operasi matematika sebelum memilih jawaban.
                      </p>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </section>
        )}
      </main>
    </div>
  );
}
