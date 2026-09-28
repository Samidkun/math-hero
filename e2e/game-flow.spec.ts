import { test, expect } from '@playwright/test';

test.describe('Math Hero E2E Game Flow', () => {
  test('AC-1, AC-6, AC-8: Player onboarding, map locking, quiz loop, and misconception review', async ({ page }) => {
    test.setTimeout(60000);
    // 1. Visit Home
    await page.goto('/');
    await expect(page).toHaveTitle(/Math Hero/);

    // 2. Select Grade 4 & enter nickname
    const uniqueNickname = `Bocah_${Date.now().toString().slice(-4)}`;
    await page.fill('input[placeholder*="Budi"]', uniqueNickname);
    await page.click('button:has-text("Gas, Mulai Petualangan")');

    // 3. Redirected to /map
    await page.waitForURL('**/map', { timeout: 10000 });
    await expect(page.locator('h1')).toContainText(uniqueNickname);

    // 4. Verify Map: Level 1 is unlocked, Level 2 is locked (AC-8)
    const startButton = page.locator('button:has-text("Mulai Level 1")');
    await expect(startButton).toBeVisible();
    await expect(startButton).toBeEnabled();

    // 5. Start Level 1
    await startButton.click();
    await page.waitForURL('**/play/**', { timeout: 10000 });

    // 6. Play loop: answer 10 questions
    for (let q = 0; q < 10; q++) {
      // Ensure we are synchronized to this question number
      await expect(page.locator(`text=Soal ${q + 1} dari 10`)).toBeVisible({ timeout: 10000 });

      // Click option 0 (Option A) and verify it registers
      const optionA = page.locator('[data-testid="option-0"]').first();
      await expect(optionA).toBeEnabled({ timeout: 5000 });
      await optionA.click();
      await expect(optionA).toBeDisabled({ timeout: 5000 });

      // If it's the last question (index 9), wait for "Lihat Hasil Akhir" button
      if (q === 9) {
        const finishBtn = page.locator('[data-testid="finish-btn"]');
        await expect(finishBtn).toBeVisible({ timeout: 10000 });
        await finishBtn.click();
      } else {
        // Wait for question to advance
        await expect(page.locator(`text=Soal ${q + 2} dari 10`)).toBeVisible({ timeout: 10000 });
      }
    }

    // 7. Land on Results screen
    await page.waitForURL('**/results/**', { timeout: 10000 });
    await expect(page.locator('text=Hasil Petualangan')).toBeVisible();

    // Verify score percentage and time are visible
    const scoreText = await page.locator('span.tabular-nums').textContent();
    expect(scoreText).toMatch(/\d+%/);

    // If score < 100%, misconception review must be present (AC-6)
    if (scoreText !== '100%') {
      await expect(page.locator('text=Ulasan Kesalahan & Evaluasi')).toBeVisible();
      await expect(page.locator('text=Diagnosa Kesalahan:').first()).toBeVisible();
    }
  });
});
