import { defineConfig, devices } from '@playwright/test';

const PORT = Number(process.env.E2E_PORT ?? 8899);
const BASE_URL = process.env.E2E_BASE_URL || `http://127.0.0.1:${PORT}`;
const PAKAI_SERVER_EKSTERNAL = Boolean(process.env.E2E_BASE_URL);

export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: 'list',

  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
  },

  projects: [
    {
      name: 'mobile-chrome',
      use: {
        ...devices['Pixel 7'],
        launchOptions: {
          executablePath: '/usr/bin/chromium',
        },
      },
    },
  ],

  webServer: PAKAI_SERVER_EKSTERNAL
    ? undefined
    : {
        command: `php artisan serve --port=${PORT}`,
        url: BASE_URL,
        reuseExistingServer: !process.env.CI,
        timeout: 30_000,
      },
});
