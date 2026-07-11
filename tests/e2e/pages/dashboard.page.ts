import { expect, type Locator, type Page } from '@playwright/test';
import { gotoAuthenticated } from '../helpers/auth-helper';

export class DashboardPage {
  readonly dashboardApp: Locator;
  readonly createDeckButton: Locator;
  readonly deckCards: Locator;
  readonly deckNameInput: Locator;
  readonly deleteDeckModal: Locator;
  readonly deleteDeckModalMessage: Locator;
  readonly deleteDeckSubmitButton: Locator;
  readonly deleteDeckFeedback: Locator;

  constructor(private readonly page: Page) {
    this.dashboardApp = page.locator('[data-dashboard-app]');
    this.createDeckButton = page.locator('[data-create-deck-button]').first();
    this.deckCards = page.locator('[data-deck-card]');
    this.deckNameInput = page.locator('#new-deck-name');
    this.deleteDeckModal = page.locator('#delete-deck-modal');
    this.deleteDeckModalMessage = page.locator('[data-delete-deck-modal-message]');
    this.deleteDeckSubmitButton = page.locator('[data-delete-deck-submit-button]');
    this.deleteDeckFeedback = page.locator('[data-delete-deck-form-feedback]');
  }

  async goto() {
    await gotoAuthenticated(this.page, '/dashboard');
    await expect(this.page).toHaveURL(/\/dashboard$/);
    await expect(this.dashboardApp).toBeVisible();
  }

  async getUserId() {
    const userId = await this.dashboardApp.getAttribute('data-dashboard-user-id');
    expect(userId, 'Dashboard should expose a user id for API-backed setup.').toBeTruthy();
    return Number(userId);
  }

  deckCardByName(deckName: string) {
    return this.deckCards.filter({
      has: this.page.locator('.dash-deck__title', { hasText: deckName }),
    });
  }

  async openCreateDeckModal() {
    await this.createDeckButton.click();
    await expect(this.deckNameInput).toBeVisible();
  }

  async createDeck(name: string, description?: string) {
    await this.openCreateDeckModal();
    await this.deckNameInput.fill(name);
    if (description) {
      await this.page.locator('#new-deck-description').fill(description);
    }
    await this.page.locator('#create-deck-submit-btn').click();
  }

  async openDeleteModalForDeck(deckName: string) {
    const card = this.deckCardByName(deckName);
    await card.getByRole('button', { name: 'Delete deck' }).click();
    await expect(this.deleteDeckModal).toBeVisible();
    return card;
  }
}
