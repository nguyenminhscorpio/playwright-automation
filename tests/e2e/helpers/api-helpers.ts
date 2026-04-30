import { expect, type APIRequestContext } from '@playwright/test';

export type DeckPayload = {
  id: number;
  name: string;
  description: string | null;
};

export type CardPayload = {
  id: number;
  deck_id: number;
  front_text: string | null;
  back_text: string | null;
  front_plain_text: string | null;
  back_plain_text: string | null;
  state: string;
};

export const createDeckViaApi = async (
  request: APIRequestContext,
  userId: number | undefined,
  name: string,
  description: string
) => {
  const response = await request.post('/api/decks', {
    data: {
      ...(userId ? { user_id: userId } : {}),
      name,
      description,
    },
  });

  expect(response.ok(), 'Deck creation via API should succeed.').toBeTruthy();
  return (await response.json()) as DeckPayload;
};

export const deleteDeckViaApi = async (
  request: APIRequestContext,
  userId: number,
  deckId: number
) => {
  const response = await request.delete(`/api/decks/${deckId}`, {
    data: {
      user_id: userId,
    },
  });

  expect.soft(response.ok(), `Cleanup should remove deck ${deckId}.`).toBeTruthy();
};

export const createCardViaApi = async (
  request: APIRequestContext,
  userId: number,
  deckId: number,
  frontText: string,
  backText: string
) => {
  const response = await request.post('/api/cards', {
    data: {
      user_id: userId,
      deck_id: deckId,
      front_text: frontText,
      back_text: backText,
    },
  });

  expect(response.ok(), 'Card creation via API should succeed.').toBeTruthy();
  return (await response.json()) as CardPayload;
};

export const rateCardViaApi = async (
  request: APIRequestContext,
  cardId: number,
  rating: 'again' | 'hard' | 'good' | 'easy'
) => {
  const response = await request.post(`/api/study/cards/${cardId}/rate`, {
    data: {
      mode: 'flip',
      rating,
    },
  });

  expect(response.ok(), `Rating card ${cardId} with ${rating} should succeed.`).toBeTruthy();
};
