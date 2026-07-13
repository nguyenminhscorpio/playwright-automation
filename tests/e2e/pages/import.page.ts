import { expect, type Locator, type Page } from '@playwright/test';
import path from 'path';
import { gotoAuthenticated } from '../helpers/auth-helper';

export class ImportPage {
  readonly deckSelect: Locator;
  readonly createDeckModal: Locator;
  readonly newDeckNameInput: Locator;
  readonly fileInput: Locator;
  readonly previewButton: Locator;
  readonly confirmButton: Locator;
  readonly swapButton: Locator;
  readonly feedback: Locator;
  readonly previewRows: Locator;

  constructor(private readonly page: Page) {
    this.deckSelect = page.locator('[data-import-deck-select]');
    this.createDeckModal = page.locator('#create-deck-modal');
    this.newDeckNameInput = page.locator('#new-deck-name');
    this.fileInput = page.locator('[data-import-file-input]');
    this.previewButton = page.getByRole('button', { name: /Preview Import/i });
    this.confirmButton = page.locator('[data-import-confirm-button]');
    this.swapButton = page.getByRole('button', { name: /Swap Front\/Back/i });
    this.feedback = page.locator('[data-import-feedback]');
    this.previewRows = page.locator('[data-import-rows-body]').last().locator('tr');
  }

  async goto() {
    await gotoAuthenticated(this.page, '/imports');
    await expect(this.page).toHaveURL(/\/imports$/);
  }

  async openCreateDeckModalFromSelect() {
    await this.deckSelect.selectOption('NEW_DECK', { force: true });
    await expect(this.createDeckModal).toBeVisible();
  }

  async createDeckFromModal(deckName: string) {
    await this.openCreateDeckModalFromSelect();
    await this.newDeckNameInput.fill(deckName);
    await this.page.getByRole('button', { name: 'Create Deck' }).click();
    await expect(this.createDeckModal).toBeHidden();
  }

  async uploadTxtContent(fileContent: string, fileName = 'test-import.txt') {
    await this.fileInput.setInputFiles({
      name: fileName,
      mimeType: 'text/plain',
      buffer: Buffer.from(fileContent),
    });
  }

  async uploadFixture(fileName: string) {
    const fixturePath = path.resolve(process.cwd(), 'tests', 'e2e', 'fixtures', fileName);
    await this.fileInput.setInputFiles(fixturePath);
  }

  async previewImport() {
    await this.previewButton.click();
    await expect(this.feedback).toContainText('Preview ready', { timeout: 15_000 });
  }

  async createDeckAndPreview(deckName: string, fileContent: string) {
    await this.createDeckFromModal(deckName);
    await this.uploadTxtContent(fileContent);
    await this.previewImport();
  }

  rowCells(rowIndex: number) {
    return this.previewRows.nth(rowIndex).locator('td');
  }

  async filterBy(name: 'All' | 'Valid' | 'Warnings' | 'Errors') {
    await this.page.getByRole('button', { name }).click();
  }
}
