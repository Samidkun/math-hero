import { useState, useEffect, useRef, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { type QuestionItem } from '@/types';

interface PlayProps {
  session_id: number;
  level: {
    id: number;
    number: number;
    title: string;
    grade: number;
  };
  questions: QuestionItem[];
}

export default function Play({ session_id, level, questions }: PlayProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [timeLeft, setTimeLeft] = useState(30);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);
  const [answerState, setAnswerState] = useState<{
    submitted: boolean;
    isCorrect: boolean | null;
    correctIndex: number | null;
    misconception: string | null;
  }>({
    submitted: false,
    isCorrect: null,
    correctIndex: null,
    misconception: null,
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [loadingNext, setLoadingNext] = useState(false);
  const startTimeRef = useRef<number>(Date.now());
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const currentQ = questions[currentIndex];
  const isLastQuestion = currentIndex === questions.length - 1;

  const submitAnswerRef = useRef<(chosen: number | null) => Promise<void>>(async () => {});

  // Submit Answer (or timeout)
  const submitAnswer = useCallback(async (chosen: number | null) => {
    if (answerState.submitted || isSubmitting) return;

    if (timerRef.current) clearInterval(timerRef.current);

    const timeTaken = Math.min(30000, Date.now() - startTimeRef.current);
    setSelectedOption(chosen);
    setIsSubmitting(true);

    try {
      const res = await fetch(`/api/sessions/${session_id}/answer`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          question_index: currentIndex,
          chosen,
          time_taken_ms: timeTaken,
        }),
      });

      const data = await res.json();
      setAnswerState({
        submitted: true,
        isCorrect: data.is_correct,
        correctIndex: data.correct_index,
        misconception: data.misconception_label,
      });

      // Auto advance or show finish button
      setTimeout(() => {
        if (!isLastQuestion) {
          handleNextQuestion();
        }
      }, 1400);
    } catch {
      alert('Gagal mengirim jawaban. Coba periksa koneksi.');
    }
  }, [answerState.submitted, currentIndex, isLastQuestion, isSubmitting, session_id]);

  submitAnswerRef.current = submitAnswer;

  // Timer loop (only runs when question index changes!)
  useEffect(() => {
    startTimeRef.current = Date.now();
    setTimeLeft(30);
    setSelectedOption(null);
    setIsSubmitting(false);
    setAnswerState({
      submitted: false,
      isCorrect: null,
      correctIndex: null,
      misconception: null,
    });

    timerRef.current = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timerRef.current!);
          submitAnswerRef.current(null); // timeout
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [currentIndex]);

  const handleNextQuestion = () => {
    if (currentIndex < questions.length - 1) {
      setCurrentIndex((i) => i + 1);
    }
  };

  const handleFinish = async () => {
    setLoadingNext(true);
    try {
      const res = await fetch(`/api/sessions/${session_id}/finish`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!res.ok) throw new Error('Gagal menutup sesi');
      router.visit(`/results/${session_id}`);
    } catch {
      alert('Gagal menyelesaikan permainan.');
      setLoadingNext(false);
    }
  };

  const handleAbandon = async () => {
    if (!confirm('Keluar dari permainan sekarang? Progres sesi ini tidak akan terekam (dianggap gagal).')) {
      return;
    }

    try {
      await fetch(`/api/sessions/${session_id}/abandon`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
    } finally {
      router.visit('/map');
    }
  };

  // Circular timer geometry
  const r = 26;
  const circumference = 2 * Math.PI * r; // 163.36
  const dashOffset = circumference * (1 - timeLeft / 30);
  const ringColor = timeLeft <= 5 ? '#C62828' : timeLeft <= 10 ? '#F9A825' : '#1565C0';

  const OPTION_LETTERS = ['A', 'B', 'C', 'D'];

  return (
    <div className="min-h-screen bg-canvas text-ink antialiased flex flex-col justify-between">
      {/* Top Header */}
      <header className="mx-auto flex w-full max-w-2xl items-center justify-between p-4 sm:px-6">
        <div>
          <span className="text-xs font-semibold uppercase tracking-wider text-ink-muted">
            Kelas {level.grade} · Level {level.number}
          </span>
          <h2 className="font-display text-lg font-bold text-ink">{level.title}</h2>
        </div>

        <button
          type="button"
          onClick={handleAbandon}
          className="rounded-full border border-line bg-surface px-3 py-1.5 text-xs font-bold text-danger transition hover:bg-danger/10"
        >
          ✕ Keluar
        </button>
      </header>

      {/* Main Question Card */}
      <main className="mx-auto w-full max-w-2xl px-4 py-4">
        <div className="relative rounded-3xl bg-surface p-6 shadow-md border border-line sm:p-8">
          {/* Top Row: Question progress and Circular Timer */}
          <div className="flex items-center justify-between">
            <span className="rounded-full bg-surface-raised border border-line px-3 py-1 text-xs font-bold font-display text-ink-muted">
              Soal {currentIndex + 1} dari {questions.length}
            </span>

            {/* Circular Timer Ring */}
            <div className="relative flex h-14 w-14 items-center justify-center">
              <svg viewBox="0 0 64 64" className="h-full w-full -rotate-90">
                <circle
                  cx="32"
                  cy="32"
                  r={r}
                  fill="none"
                  stroke="rgba(34,48,31,0.1)"
                  strokeWidth="5"
                />
                <circle
                  cx="32"
                  cy="32"
                  r={r}
                  fill="none"
                  stroke={ringColor}
                  strokeWidth="5"
                  strokeLinecap="round"
                  strokeDasharray={circumference}
                  strokeDashoffset={dashOffset}
                  className="transition-all duration-1000 ease-linear"
                />
              </svg>
              <span className="absolute font-display text-base font-bold text-ink">
                {timeLeft}
              </span>
            </div>
          </div>

          {/* Question Stem */}
          <h1 className="mt-4 font-display text-2xl font-bold leading-snug sm:text-3xl text-ink">
            {currentQ?.stem}
          </h1>

          {/* 4 Option Buttons */}
          <div className="mt-6 flex flex-col gap-3">
            {currentQ?.options.map((optText, idx) => {
              const isSelected = selectedOption === idx;
              const isCorrectAnswer = answerState.correctIndex === idx;
              const isWrongSelection = isSelected && answerState.isCorrect === false;

              let btnStyle = 'border-line bg-surface hover:border-focus hover:scale-[1.01]';
              if (answerState.submitted) {
                if (isCorrectAnswer) {
                  btnStyle = 'border-success bg-success/10 font-bold text-success';
                } else if (isWrongSelection) {
                  btnStyle = 'border-danger bg-danger/10 text-danger';
                } else {
                  btnStyle = 'border-line bg-surface/50 opacity-40';
                }
              }

              return (
                <button
                  key={idx}
                  data-testid={`option-${idx}`}
                  type="button"
                  disabled={answerState.submitted || isSubmitting}
                  onClick={() => submitAnswer(idx)}
                  className={`flex min-h-[48px] w-full items-center gap-3.5 rounded-2xl border-2 px-4 py-3.5 text-left transition ${btnStyle}`}
                >
                  <span className="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-canvas font-display text-sm font-bold text-ink">
                    {OPTION_LETTERS[idx]}
                  </span>
                  <span className="flex-1 font-display text-lg text-ink font-medium">
                    {optText}
                  </span>

                  {answerState.submitted && isCorrectAnswer && (
                    <span className="text-success font-bold text-xl">✓</span>
                  )}
                  {answerState.submitted && isWrongSelection && (
                    <span className="text-danger font-bold text-xl">✕</span>
                  )}
                </button>
              );
            })}
          </div>

          {/* Feedback & Misconception banner */}
          {answerState.submitted && (
            <div
              className={`mt-5 rounded-2xl p-4 text-sm font-medium border ${
                answerState.isCorrect
                  ? 'border-success/30 bg-success/10 text-success'
                  : 'border-danger/30 bg-danger/10 text-danger'
              }`}
            >
              {answerState.isCorrect ? (
                <div className="flex items-center gap-2 font-display font-bold text-base">
                  <span>✓ Hebat! Jawabanmu benar!</span>
                </div>
              ) : (
                <div>
                  <div className="font-display font-bold text-base">
                    ✕ {selectedOption === null ? 'Waktu habis!' : 'Kurang tepat!'}
                  </div>
                  {answerState.misconception && (
                    <p className="mt-1 text-xs text-ink-muted">
                      Catatan evaluasi: Perhatikan kembali konsep pada soal ini agar lebih teliti.
                    </p>
                  )}
                </div>
              )}
            </div>
          )}

          {/* Action Row when finished */}
          {answerState.submitted && isLastQuestion && (
            <div className="mt-6 border-t border-line pt-4">
              <button
                data-testid="finish-btn"
                type="button"
                disabled={loadingNext}
                onClick={handleFinish}
                className="w-full rounded-2xl bg-hero py-4 font-display text-lg font-bold text-hero-ink shadow-md transition hover:scale-[1.02] active:scale-[0.98]"
              >
                {loadingNext ? 'Menghitung Skor...' : 'Lihat Hasil Akhir 🏆'}
              </button>
            </div>
          )}
        </div>
      </main>

      {/* Footer Info */}
      <footer className="p-4 text-center text-xs text-ink-muted">
        Ingat: Untuk membuka level selanjutnya, kamu wajib meraih nilai 100%!
      </footer>
    </div>
  );
}
