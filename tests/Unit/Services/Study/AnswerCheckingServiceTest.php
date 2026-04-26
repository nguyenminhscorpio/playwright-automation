<?php

namespace Tests\Unit\Services\Study;

use App\Services\Study\AnswerCheckingService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AnswerCheckingServiceTest extends TestCase
{
    private AnswerCheckingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnswerCheckingService();
    }

    // ─── Normal Cases ───────────────────────────────────────────

    #[Test]
    public function given_exact_match_when_check_then_correct(): void
    {
        // Arrange
        $userAnswer = 'hello';
        $correctAnswer = 'hello';

        // Act
        $result = $this->service->check($userAnswer, $correctAnswer);

        // Assert
        $this->assertSame('correct', $result['result']);
        $this->assertSame('hello', $result['user_answer']);
        $this->assertSame('hello', $result['correct_answer']);
    }

    #[Test]
    public function given_case_difference_when_check_then_correct(): void
    {
        // Arrange & Act
        $result = $this->service->check('Hello World', 'hello world');

        // Assert
        $this->assertSame('correct', $result['result']);
    }

    #[Test]
    public function given_similar_answer_when_check_then_close_match(): void
    {
        // Arrange — "to eat" vs "to eат" style: close but not exact
        $result = $this->service->check('to eat food', 'to eat foods');

        // Assert
        $this->assertSame('close_match', $result['result']);
    }

    #[Test]
    public function given_very_different_answer_when_check_then_incorrect(): void
    {
        // Arrange & Act
        $result = $this->service->check('cat', 'elephant');

        // Assert
        $this->assertSame('incorrect', $result['result']);
    }

    // ─── Edge Cases ─────────────────────────────────────────────

    #[Test]
    public function given_extra_whitespace_when_check_then_correct(): void
    {
        // Arrange — multiple spaces, leading/trailing
        $result = $this->service->check('  hello   world  ', 'hello world');

        // Assert
        $this->assertSame('correct', $result['result']);
    }

    #[Test]
    public function given_special_chars_when_check_then_correct(): void
    {
        // Arrange — punctuation stripped by normalize
        $result = $this->service->check('hello!', 'hello');

        // Assert
        $this->assertSame('correct', $result['result']);
    }

    #[Test]
    public function given_empty_strings_when_check_then_correct(): void
    {
        // Arrange & Act
        $result = $this->service->check('', '');

        // Assert — both normalize to "", exact match
        $this->assertSame('correct', $result['result']);
    }

    #[Test]
    public function given_unicode_japanese_when_check_then_correct(): void
    {
        // Arrange
        $result = $this->service->check('食べる', '食べる');

        // Assert
        $this->assertSame('correct', $result['result']);
    }

    // ─── Boundary Tests (85% threshold) ────────────────────────

    #[Test]
    public function given_below_85_percent_similarity_when_check_then_incorrect(): void
    {
        // "ab" vs "xyz" → very low similarity → incorrect
        $result = $this->service->check('ab', 'xyz');

        $this->assertSame('incorrect', $result['result']);
    }

    #[Test]
    public function given_above_85_percent_similarity_when_check_then_close_match(): void
    {
        // "abcdefghij" vs "abcdefghik" → 90% similar → close_match
        $result = $this->service->check('abcdefghij', 'abcdefghik');

        $this->assertSame('close_match', $result['result']);
    }

    #[Test]
    public function given_check_when_called_then_returns_normalized_user_answer_key(): void
    {
        // Verify the response structure includes normalized_user_answer
        $result = $this->service->check('  Hello!  ', 'hello');

        $this->assertArrayHasKey('normalized_user_answer', $result);
        $this->assertSame('hello', $result['normalized_user_answer']);
        $this->assertSame('  Hello!  ', $result['user_answer']); // original preserved
    }

    // ─── Normalize Tests ────────────────────────────────────────

    #[Test]
    public function given_mixed_case_unicode_when_normalize_then_lowercased(): void
    {
        // Arrange & Act
        $normalized = $this->service->normalize('ÜBER Cool');

        // Assert
        $this->assertSame('über cool', $normalized);
    }

    #[Test]
    public function given_multiple_spaces_and_special_chars_when_normalize_then_cleaned(): void
    {
        // Arrange & Act
        $normalized = $this->service->normalize('  hello,   world!  How?  ');

        // Assert — special chars → space, multiple spaces → single, trimmed
        $this->assertSame('hello world how', $normalized);
    }
}
