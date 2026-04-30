import { expect, type Locator, type Page } from '@playwright/test';
import path from 'path';

export class ImportPage {
  readonly deckSelect: Locator;
  readonly createDeckModal: Locator;
  readonly newDeckNameInput: Locator;
  readonly fileInput: Locator;
  readonly previewButton: Locator;
  readonly confirmButton: Locator;
  readonly feedback: Locator;
  readonly previewRows: Locator;

  constructor(private readonly page: Page) {
    this.deckSelect = page.locator('[data-import-deck-select]');
    this.createDeckModal = page.locator('#create-deck-modal');
    this.newDeckNameInput = page.locator('#new-deck-name');
    this.fileInput = page.locator('[data-import-file-input]');
    this.previewButton = page.getByRole('button', { name: /Preview Import/i });
    this.confirmButton = page.getByRole('button', { name: /Confirm Import/i });
    this.feedback = page.locator('[data-import-feedback]');
    this.previewRows = page.locator('[data-import-rows-body] tr');
  }

  async goto() {
    await this.page.goto('/imports');
    await expect(this.page).toHaveURL(/\/imports$/);
  }

  async openCreateDeckModalFromSelect() {
    await this.deckSelect.selectOption('NEW_DECK');
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
