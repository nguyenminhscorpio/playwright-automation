import { expect, type Locator, type Page } from '@playwright/test';

export class DeckDetailPage {
  readonly app: Locator;
  readonly cardRows: Locator;
  readonly cardModal: Locator;
  readonly deleteCardModal: Locator;
  readonly bulkDeleteButton: Locator;
  readonly selectAllCheckbox: Locator;
  readonly deckSwitcher: Locator;
  readonly searchInput: Locator;
  readonly applyButton: Locator;

  constructor(private readonly page: Page) {
    this.app = page.locator('[data-deck-detail-app]');
    this.cardRows = page.locator('[data-card-row]');
    this.cardModal = page.locator('#card-modal');
    this.deleteCardModal = page.locator('#delete-card-modal');
    this.bulkDeleteButton = page.locator('[data-action-bulk-delete]');
    this.selectAllCheckbox = page.locator('[data-select-all-checkbox]');
    this.deckSwitcher = page.locator('[data-deck-switcher]');
    this.searchInput = page.getByPlaceholder('Search cards by front, back, or description...');
    this.applyButton = page.getByRole('button', { name: 'Apply' });
  }

  async goto(deckId: number) {
    await this.page.goto(`/decks/${deckId}`);
    await expect(this.page).toHaveURL(new RegExp(`/decks/${deckId}$`));
    await expect(this.app).toBeVisible();
  }

  rowByFrontText(frontText: string) {
    return this.cardRows.filter({ hasText: frontText });
  }

  async search(keyword: string) {
    await this.searchInput.fill(keyword);
    await this.applyButton.click();
  }

  async filterByStatus(status: 'new' | 'learning' | 'review') {
    await this.page.locator('select[name="status"]').selectOption(status);
    await this.applyButton.click();
  }

  async openCreateCardModal() {
    await this.page.getByRole('button', { name: 'Create Card' }).click();
    await expect(this.cardModal).toBeVisible();
  }

  async fillCardModal(frontText: string, backText: string) {
    await this.page.locator('[data-card-front-input]').fill(frontText);
    await this.page.locator('[data-card-back-input]').fill(backText);
  }

  async submitCardModal() {
    await this.page.locator('[data-card-submit-button]').click();
  }

  async openEditCardModal(frontText: string) {
    await this.rowByFrontText(frontText).getByRole('button', { name: 'Edit card' }).click();
    await expect(this.cardModal).toBeVisible();
  }

  async openDeleteCardModal(frontText: string) {
    await this.rowByFrontText(frontText).getByRole('button', { name: 'Delete card' }).click();
    await expect(this.deleteCardModal).toBeVisible();
  }

  async selectCard(frontText: string) {
    await this.rowByFrontText(frontText).locator('[data-row-checkbox]').check();
  }

  async unselectCard(frontText: string) {
    await this.rowByFrontText(frontText).locator('[data-row-checkbox]').uncheck();
  }
}
