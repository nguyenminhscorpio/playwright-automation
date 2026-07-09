import { expect, test } from '@playwright/test';
import { ImportPage } from './pages/import.page';

test.describe('Import Flow', () => {
  test.beforeEach(async ({ page }) => {
    await new ImportPage(page).goto();
  });

  test('Should display preview after uploading a valid TXT file', async ({ page }) => {
    const importPage = new ImportPage(page);

    await importPage.createDeckFromModal(`Test Deck ${Date.now()}`);
    await importPage.uploadFixture('sample-import.txt');
    await importPage.previewImport();

    await expect(importPage.previewRows).toHaveCount(2);
    await expect(importPage.previewRows.nth(0)).toContainText('Front 1');
    await expect(importPage.previewRows.nth(0)).toContainText('Back 1');
    await expect(importPage.previewRows.nth(1)).toContainText('Front 2');
    await expect(importPage.previewRows.nth(1)).toContainText('Back 2');
  });

  test('Should filter rows based on validity', async ({ page }) => {
    const importPage = new ImportPage(page);

    await importPage.createDeckAndPreview(
      `Test Deck ${Date.now()}`,
      'Front 1\tBack 1\n' +
        'Front 2\t[sound:test.mp3]\tBack 2\n' +
        'Front 3\t'
    );

    await expect(importPage.previewRows).toHaveCount(3);

    await importPage.filterBy('Valid');
    await expect(importPage.previewRows).toHaveCount(1);
    await expect(importPage.rowCells(0)).toHaveCount(5);
    await expect(importPage.rowCells(0).nth(1)).toContainText('Front 1');
    await expect(importPage.rowCells(0).nth(2)).toContainText('Back 1');
    await expect(importPage.rowCells(0).nth(3)).toContainText('valid');

    await importPage.filterBy('Warnings');
    await expect(importPage.previewRows).toHaveCount(1);
    await expect(importPage.rowCells(0).nth(1)).toContainText('Front 2');
    await expect(importPage.rowCells(0).nth(2)).toContainText('Back 2');
    await expect(importPage.rowCells(0).nth(3)).toContainText('warning');
    await expect(importPage.rowCells(0).nth(4)).toContainText('Audio token was detected');

    await importPage.filterBy('Errors');
    await expect(importPage.previewRows).toHaveCount(1);
    await expect(importPage.rowCells(0).nth(3)).toContainText('invalid');
    await expect(importPage.rowCells(0).nth(4)).toContainText('at least 2 text fields');

    await importPage.filterBy('All');
    await expect(importPage.previewRows).toHaveCount(3);
  });

  test('Should swap front and back in row preview', async ({ page }) => {
    const importPage = new ImportPage(page);

    await importPage.createDeckAndPreview(
      `Test Deck ${Date.now()}`,
      'Front A\tBack A\n'
    );

    await importPage.swapButton.click();

    await expect(importPage.rowCells(0).nth(1)).toContainText('Back A');
    await expect(importPage.rowCells(0).nth(2)).toContainText('Front A');
  });

  test('Should show success message after confirming import', async ({ page }) => {
    const importPage = new ImportPage(page);

    await importPage.createDeckFromModal(`Test Deck ${Date.now()}`);
    await importPage.uploadFixture('sample-import.txt');
    await importPage.previewImport();

    await expect(importPage.confirmButton).toBeEnabled();
    await importPage.confirmButton.click();

    await expect(importPage.feedback).toBeVisible();
    await expect(importPage.feedback).toContainText('Import complete');
    await expect(importPage.feedback).toContainText('Imported 2 rows');
  });

  test('Should disable confirm button after import', async ({ page }) => {
    const importPage = new ImportPage(page);

    await importPage.createDeckFromModal(`Test Deck ${Date.now()}`);
    await importPage.uploadFixture('sample-import.txt');
    await importPage.previewImport();

    await expect(importPage.confirmButton).toBeEnabled();
    await importPage.confirmButton.click();
    await expect(importPage.confirmButton).toBeDisabled();
  });

  test('Should allow selecting target deck from dropdown', async ({ page }) => {
    const importPage = new ImportPage(page);
    const firstDeckName = `Existing Deck 1 ${Date.now()}`;
    const secondDeckName = `Existing Deck 2 ${Date.now()}`;

    await importPage.createDeckFromModal(firstDeckName);
    await importPage.createDeckFromModal(secondDeckName);

    await importPage.deckSelect.selectOption({ label: firstDeckName });
    await expect(importPage.deckSelect).toHaveValue(/^\d+$/);
    await expect(importPage.deckSelect.locator('option:checked')).toHaveText(firstDeckName);

    await importPage.deckSelect.selectOption({ label: secondDeckName });
    await expect(importPage.deckSelect.locator('option:checked')).toHaveText(secondDeckName);
  });
});
