import { Link } from '@inertiajs/react';

interface StudentItem {
  id: number;
  nickname: string;
  current_level: number;
  perfect_levels: number;
  total_stars: number;
}

interface ClassroomProps {
  classroom: {
    code: string;
    name: string;
    grade: number;
    teacher_name: string;
  };
  students: StudentItem[];
}

export default function Classroom({ classroom, students }: ClassroomProps) {
  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-5xl items-center justify-between p-4 sm:px-8">
        <Link href="/" className="flex items-center gap-2 font-display text-lg font-bold text-hero hover:opacity-90">
          <span>← Beranda</span>
        </Link>
        <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
          Portal Guru
        </span>
      </header>

      <main className="mx-auto max-w-5xl px-4 py-6 sm:py-10">
        {/* Header Information */}
        <div className="rounded-3xl bg-surface p-6 shadow-md border border-line sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
              Kelas {classroom.grade} · Guru: {classroom.teacher_name}
            </span>
            <h1 className="mt-1 font-display text-3xl font-bold sm:text-4xl text-ink">
              {classroom.name}
            </h1>
            <p className="mt-1 text-sm text-ink-muted">
              Pantau progres siswa dan analisis kelemahan konsep matematika secara real-time.
            </p>
          </div>

          <div className="flex flex-col items-start sm:items-end">
            <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
              Kode Bergabung
            </span>
            <span className="mt-1 font-display text-3xl font-bold tracking-widest text-focus select-all bg-surface-raised px-4 py-1.5 rounded-2xl border border-line">
              {classroom.code}
            </span>
          </div>
        </div>

        {/* Roster Section */}
        <section className="mt-8">
          <div className="flex items-center justify-between mb-4 px-2">
            <h2 className="font-display text-2xl font-bold text-ink">
              Daftar Siswa ({students.length})
            </h2>
            <span className="text-xs text-ink-muted font-medium">
              Klik nama siswa untuk melihat laporan evaluasi
            </span>
          </div>

          {students.length === 0 ? (
            <div className="rounded-3xl bg-surface p-12 text-center border border-line shadow-sm">
              <p className="font-display text-xl font-bold text-ink">Belum Ada Siswa yang Bergabung</p>
              <p className="mt-1 text-sm text-ink-muted">
                Bagikan kode kelas <b>{classroom.code}</b> kepada murid-muridmu agar mereka dapat masuk.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {students.map((student) => {
                const initial = student.nickname.charAt(0).toUpperCase();

                return (
                  <div
                    key={student.id}
                    className="flex flex-col justify-between rounded-2xl bg-surface p-5 shadow-sm border border-line transition hover:border-focus"
                  >
                    <div>
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-canvas font-display text-base font-bold text-ink border border-line">
                            {initial}
                          </div>
                          <div>
                            <h3 className="font-display text-lg font-bold text-ink leading-tight">
                              {student.nickname}
                            </h3>
                            <span className="text-xs text-ink-muted">Siswa</span>
                          </div>
                        </div>

                        {/* Level badge */}
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-surface-raised font-display text-base font-bold text-hero border-2 border-hero">
                          {student.current_level}
                        </div>
                      </div>

                      {/* Stat chips */}
                      <div className="mt-4 flex flex-wrap gap-2 text-xs">
                        <span className="inline-flex items-center gap-1 rounded-full bg-surface-raised px-2.5 py-1 font-display font-bold text-gold border border-line">
                          ★ {student.total_stars} Bintang
                        </span>
                        <span className="inline-flex items-center gap-1 rounded-full bg-surface-raised px-2.5 py-1 font-display font-bold text-hero border border-line">
                          ✓ {student.perfect_levels} Sempurna
                        </span>
                      </div>
                    </div>

                    <div className="mt-5 border-t border-line pt-3">
                      <Link
                        href={`/report/${classroom.code}/${student.id}`}
                        className="inline-flex items-center gap-1 text-xs font-bold text-focus hover:underline"
                      >
                        Lihat Laporan Diagnostik →
                      </Link>
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
