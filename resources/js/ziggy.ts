/// <reference types="vite/client" />

import { route } from 'ziggy-js';
import { Ziggy } from './ziggy-config';

if (typeof globalThis !== 'undefined') {
  (globalThis as Record<string, unknown>).Ziggy = Ziggy;
}

export { route };
