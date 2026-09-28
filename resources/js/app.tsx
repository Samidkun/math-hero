import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from './ziggy';
import { type ReactNode } from 'react';

const appName = import.meta.env.VITE_APP_NAME || 'Math Hero';

declare global {
  interface Window {
    route: typeof route;
  }
}

type PageComponent = (props: Record<string, unknown>) => ReactNode;

createInertiaApp({
  title: (title) => (title ? `${title} — ${appName}` : appName),
  resolve: async (name): Promise<PageComponent> => {
    const mod = await resolvePageComponent(
      `./Pages/${name}.tsx`,
      import.meta.glob('./Pages/**/*.tsx'),
    );
    return mod as unknown as PageComponent;
  },
  setup({ el, App, props }) {
    createRoot(el as HTMLElement).render(<App {...props} />);
  },
  progress: { color: '#2F6B4F' },
});
