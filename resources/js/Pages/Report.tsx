import { Link } from '@inertiajs/react';

interface ReportProps {
  classroom: {
    code: string;
    name: string;
  };
  player: {
    id: number;
    nickname: string;
  };
  misconceptions: Record<string, Record<string, number>>;
}

export default function Report({ classroom, player, misconceptions }: ReportProps) {
  const topicEntries = Object.entries(misconceptions);

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-5xl items-center justify-between p-4 sm:px-8">
        <Link
          href={`/classroom/${classroom.code}`}
          className="flex items-center gap-2 font-display text-lg font-bold text-hero hover:opacity-90"
        >
          <span>← Kembali ke Kelas {classroom.name}</span>
        </Link>
        <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
          Laporan Diagnostik
        </span>
      </header>

      <main className="mx-auto max-w-5xl px-4 py-6 sm:py-10">
        {/* Student Overview Card */}
        <div className="rounded-3xl bg-surface p-6 shadow-md border border-line sm:p-8">
          <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
            Analisis Penguasaan Konsep Siswa
          </span>
          <h1 className="mt-1 font-display text-3xl font-bold sm:text-4xl text-ink">
            {player.nickname}
          </h1>
          <p className="mt-1 text-sm text-ink-muted">
            Distribusi kesalahan konsep (misconception) yang terdeteksi saat mengerjakan soal matematika.
          </p>
        </div>

        {/* Diagnostic Topics */}
        <section className="mt-8">
          <div className="flex items-center justify-between mb-4 px-2">
            <h2 className="font-display text-2xl font-bold text-ink">
              Peta Diagnosa Kesalahan
            </h2>
            <span className="text-xs text-ink-muted font-medium">
              Berdasarkan jawaban salah pilihan ganda
            </span>
          </div>

          {topicEntries.length === 0 ? (
            <div className="rounded-3xl bg-surface p-12 text-center border border-line shadow-sm">
              <p className="font-display text-xl font-bold text-success">
                Belum Terdeteksi Pola Kesalahan yang Menonjol 🎉
              </p>
              <p className="mt-1 text-sm text-ink-muted max-w-md mx-auto">
                Siswa belum banyak mengerjakan soal atau performanya konsisten menjawab benar.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
              {topicEntries.map(([topicCode, labelsMap]) => {
                const labels = Object.entries(labelsMap);
                const maxCount = Math.max(...labels.map(([, count]) => count), 1);

                return (
                  <div
                    key={topicCode}
                    className="rounded-2xl bg-surface p-6 shadow-sm border border-line flex flex-col justify-between"
                  >
                    <div>
                      <div className="flex items-center justify-between mb-4 border-b border-line pb-2">
                        <h3 className="font-display text-lg font-bold text-ink">
                          Topik: {topicCode.replace('grade-', 'Kelas ').replace('/', ' · ')}
                        </h3>
                        <span className="text-xs font-semibold uppercase tracking-wider text-accent-earth bg-accent-earth/10 px-2 py-0.5 rounded-full">
                          {labels.reduce((acc, [, c]) => acc + c, 0)} kali keliru
                        </span>
                      </div>

                      {/* Bar Rows */}
                      <div className="space-y-3">
                        {labels.map(([lbl, count]) => {
                          const pct = (count / maxCount) * 100;
                          const isLeading = count === maxCount;

                          return (
                            <div key={lbl} className="text-xs">
                              <div className="flex justify-between font-semibold mb-1">
                                <span className={isLeading ? 'text-danger' : 'text-ink'}>
                                  {lbl}
                                </span>
                                <span className="font-display tabular-nums text-ink-muted">
                                  {count}x
                                </span>
                              </div>
                              <div className="h-2.5 w-full overflow-hidden rounded-full bg-canvas border border-line">
                                <div
                                  className={`h-full rounded-full transition-all duration-500 ${
                                    isLeading ? 'bg-danger' : 'bg-accent-earth'
                                  }`}
                                  style={{ width: `${pct}%` }}
                                />
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    <div className="mt-5 rounded-xl bg-surface-raised p-3 border border-line text-xs text-ink-muted">
                      💡 <b>Rekomendasi Guru:</b> Lakukan remidi terfokus pada pengenalan konsep di atas sebelum mengulangi kuis.
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </section>
      </main>
    </div>
  );
}
