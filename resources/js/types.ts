import { type PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Player {
  id: number;
  nickname: string;
  class_id: number | null;
  class_code?: string;
  class_name?: string;
  total_stars: number;
}

export interface ProgressSummary {
  unlocked_levels: number;
  perfect_levels: number;
  total_stars: number;
  next_level: number;
}

export interface LevelItem {
  id: number;
  number: number;
  title: string;
  grade: number;
  is_unlocked: boolean;
  best_score_pct: number;
  best_time_ms: number | null;
  is_perfect: boolean;
}

export interface QuestionItem {
  index: number;
  stem: string;
  options: string[];
}

export interface WrongReview {
  index: number;
  stem: string;
  chosen_text: string | null;
  correct_text: string;
  misconception: string | null;
}

export interface PageProps extends InertiaPageProps {
  auth: { player: Player | null };
  flash: { message?: string };
}
