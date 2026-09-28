export default function Play() {
  return (
    <main className="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-6">
      <div className="w-full rounded-3xl bg-surface p-8 shadow-2xl">
        <h1 className="font-display text-3xl text-leaf-deep">Level 1 · Bilangan Cacah</h1>
        <p className="mt-2 text-ink-soft">Soal 1 dari 10</p>
        <p className="mt-6 font-display text-2xl">Berapa nilai tempat angka 5 pada 85.432?</p>
        <div className="mt-6 grid gap-3">
          {['500', '5.000', '50', '5'].map((o, i) => (
            <button
              key={i}
              className="rounded-xl border-2 border-line bg-surface-raised px-6 py-4 text-left font-display text-xl transition hover:scale-[1.02] hover:border-leaf"
            >
              {o}
            </button>
          ))}
        </div>
      </div>
    </main>
  );
}
