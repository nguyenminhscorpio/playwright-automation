<?php

namespace App\Services\Study;

class AnswerCheckingService
{
    public function check(string $userAnswer, string $correctAnswer): array
    {
        $normalizedUserAnswer = $this->normalize($userAnswer);
        $normalizedCorrectAnswer = $this->normalize($correctAnswer);

        if ($normalizedUserAnswer === $normalizedCorrectAnswer) {
            $similarityPercent = 100.0;
            $result = 'correct';
        } else {
            similar_text($normalizedUserAnswer, $normalizedCorrectAnswer, $similarityPercent);
            $result = ($similarityPercent / 100) >= 0.85 ? 'close_match' : 'incorrect';
        }

        return [
            'correct_answer' => $correctAnswer,
            'user_answer' => $userAnswer,
            'normalized_user_answer' => $normalizedUserAnswer,
            'similarity_percent' => round($similarityPercent, 1),
            'result' => $result,
        ];
    }

    public function normalize(string $value): string
    {
        $normalized = mb_strtolower($value);
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
