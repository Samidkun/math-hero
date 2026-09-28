import { Link } from '@inertiajs/react';
import { type PageProps } from '@/types';

export default function Home({ auth }: PageProps) {
  return (
    <main className="mx-auto flex min-h-screen max-w-2xl flex-col items-center justify-center gap-8 p-6">
      <div className="text-center">
        <h1 className="font-display text-5xl text-leaf-deep">Math Hero</h1>
        <p className="mt-2 text-ink-soft">Petualangan matematika SD kelas 4-6</p>
      </div>
      <Link
        href="/play"
        className="rounded-2xl bg-leaf px-8 py-4 font-display text-2xl text-white shadow-lg transition hover:scale-105"
      >
        Mulai Bermain
      </Link>
      {auth?.player && (
        <p className="text-ink-soft">Halo, {auth.player.nickname}!</p>
      )}
    </main>
  );
}
