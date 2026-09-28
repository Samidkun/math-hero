import { type PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Player {
  id: number;
  nickname: string;
  classId: number | null;
  totalStars: number;
}

export interface PageProps extends InertiaPageProps {
  auth: { player: Player | null };
}
