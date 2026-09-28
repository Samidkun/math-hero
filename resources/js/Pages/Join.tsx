import { useState, useRef } from 'react';
import { Link, router } from '@inertiajs/react';

export default function Join() {
  const [codeCells, setCodeCells] = useState<string[]>(['', '', '', '', '', '']);
  const [nickname, setNickname] = useState('');
  const [grade, setGrade] = useState<number>(4);
  const [pin, setPin] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

  const handleCellChange = (index: number, value: string) => {
    const char = value.slice(-1).toUpperCase().replace(/[^A-Z0-9]/g, '');
    const newCells = [...codeCells];
    newCells[index] = char;
    setCodeCells(newCells);

    // Auto advance focus
    if (char && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Backspace' && !codeCells[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const fullCode = codeCells.join('');

  const handleJoin = async (e: React.FormEvent) => {
    e.preventDefault();
    if (fullCode.length !== 6 || !nickname.trim()) return;

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
          class_code: fullCode,
          grade,
          pin: pin ? pin : undefined,
        }),
      });

      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || 'Gagal bergabung ke kelas');
      }

      router.visit('/map');
    } catch (err: unknown) {
      setErrorMsg(err instanceof Error ? err.message : 'Terjadi kesalahan');
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased">
      {/* Topbar */}
      <header className="mx-auto flex max-w-xl items-center justify-between p-4 sm:px-6">
        <Link href="/" className="flex items-center gap-2 font-display text-lg font-bold text-hero hover:opacity-90">
          <span>← Kembali</span>
        </Link>
        <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
          Gabung Kelas
        </span>
      </header>

      <main className="mx-auto max-w-xl px-4 py-6 sm:py-10">
        <div className="rounded-3xl bg-surface p-6 shadow-md border border-line sm:p-10">
          <div className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
            Siswa / Siswi
          </div>

          <h1 className="mt-2 font-display text-3xl font-bold sm:text-4xl text-ink">
            Gabung Kelas Lewat Kode
          </h1>
          <p className="mt-1 text-sm text-ink-muted">
            Minta kode 6 huruf/angka dari gurumu atau orang tua, lalu masukkan nama panggilanmu.
          </p>

          <form onSubmit={handleJoin} className="mt-6 space-y-5">
            {errorMsg && (
              <div className="rounded-xl bg-danger/10 p-3.5 text-sm font-semibold text-danger border border-danger/20">
                {errorMsg}
              </div>
            )}

            {/* 6-Character Code Grid */}
            <div>
              <label className="block text-sm font-semibold text-ink mb-2">
                Kode Kelas (6 Karakter)
              </label>
              <div className="grid grid-cols-6 gap-2 sm:gap-3">
                {codeCells.map((char, idx) => (
                  <input
                    key={idx}
                    ref={(el) => {
                      inputRefs.current[idx] = el;
                    }}
                    type="text"
                    maxLength={1}
                    value={char}
                    placeholder="·"
                    onChange={(e) => handleCellChange(idx, e.target.value)}
                    onKeyDown={(e) => handleKeyDown(idx, e)}
                    className="h-12 sm:h-14 w-full rounded-xl border-2 border-line bg-surface text-center font-display text-2xl font-bold uppercase text-ink outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20"
                  />
                ))}
              </div>
            </div>

            {/* Nickname */}
            <div>
              <label className="block text-sm font-semibold text-ink">
                Nama Panggilan
              </label>
              <input
                type="text"
                required
                maxLength={32}
                value={nickname}
                onChange={(e) => setNickname(e.target.value)}
                placeholder="Contoh: Budi, Sari"
                className="mt-1.5 w-full rounded-xl border-2 border-line bg-surface px-4 py-3 text-base text-ink outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20"
              />
            </div>

            {/* Grade */}
            <div>
              <label className="block text-sm font-semibold text-ink">
                Tingkat Kelas
              </label>
              <div className="mt-1.5 grid grid-cols-3 gap-2">
                {[4, 5, 6].map((g) => (
                  <button
                    key={g}
                    type="button"
                    onClick={() => setGrade(g)}
                    className={`min-h-[44px] rounded-xl border-2 p-2.5 text-center font-display font-bold transition ${
                      grade === g
                        ? 'border-hero bg-surface-raised text-hero'
                        : 'border-line bg-surface text-ink'
                    }`}
                  >
                    Kelas {g}
                  </button>
                ))}
              </div>
            </div>

            {/* Optional PIN */}
            <div>
              <label className="block text-sm font-semibold text-ink">
                PIN 4 Angka (Opsional)
              </label>
              <input
                type="password"
                maxLength={4}
                value={pin}
                onChange={(e) => setPin(e.target.value.replace(/\D/g, ''))}
                placeholder="Contoh: 1234"
                className="mt-1.5 w-full rounded-xl border-2 border-line bg-surface px-4 py-3 text-base text-ink outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20"
              />
              <p className="mt-1 text-xs text-ink-muted">
                Untuk melindungi akunmu jika menggunakan perangkat bersama di sekolah.
              </p>
            </div>

            {/* Submit */}
            <button
              type="submit"
              disabled={submitting || fullCode.length !== 6 || !nickname.trim()}
              className="mt-6 w-full rounded-2xl bg-hero py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-locked"
            >
              {submitting ? 'Memeriksa Kode...' : 'Masuk ke Kelas 🚀'}
            </button>
          </form>
        </div>
      </main>
    </div>
  );
}
