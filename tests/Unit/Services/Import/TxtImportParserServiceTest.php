<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\TxtImportParserService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TxtImportParserServiceTest extends TestCase
{
    private TxtImportParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TxtImportParserService();
    }

    // ═══════════════════════════════════════════════════════════
    // A. parseContent() — Line Parsing
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_valid_tab_line_when_parse_then_extracts_front_back(): void
    {
        $content = "hello\tworld";

        $result = $this->service->parseContent($content);

        $this->assertSame(1, $result['data_lines']);
        $this->assertSame(1, $result['valid_rows']);
        $this->assertSame(0, $result['invalid_rows']);
        $this->assertSame('hello', $result['rows'][0]['parsed_front']);
        $this->assertSame('world', $result['rows'][0]['parsed_back']);
    }

    #[Test]
    public function given_3_fields_when_parse_then_back_joins_remaining(): void
    {
        $content = "front\tback1\tback2";

        $result = $this->service->parseContent($content);

        $this->assertSame('front', $result['rows'][0]['parsed_front']);
        $this->assertSame('back1 back2', $result['rows'][0]['parsed_back']);
    }

    #[Test]
    public function given_single_field_when_parse_then_marks_invalid(): void
    {
        $content = "only_one_field";

        $result = $this->service->parseContent($content);

        $this->assertSame(1, $result['data_lines']);
        $this->assertSame(0, $result['valid_rows']);
        $this->assertSame(1, $result['invalid_rows']);
        $this->assertSame('invalid', $result['rows'][0]['status']);
    }

    #[Test]
    public function given_empty_lines_when_parse_content_then_skips(): void
    {
        $content = "\n\nhello\tworld\n\n";

        $result = $this->service->parseContent($content);

        $this->assertSame(1, $result['data_lines']);
        $this->assertCount(1, $result['rows']);
    }

    #[Test]
    public function given_comment_lines_when_parse_content_then_skips(): void
    {
        $content = "# This is a comment\nhello\tworld\n#another comment";

        $result = $this->service->parseContent($content);

        $this->assertSame(1, $result['data_lines']);
        $this->assertSame('hello', $result['rows'][0]['parsed_front']);
    }

    #[Test]
    public function given_crlf_line_endings_when_parse_content_then_handles_correctly(): void
    {
        // Arrange — Windows-style \r\n
        $content = "hello\tworld\r\nfoo\tbar\r\n";

        $result = $this->service->parseContent($content);

        $this->assertSame(2, $result['data_lines']);
        $this->assertSame(2, $result['valid_rows']);
        $this->assertSame('hello', $result['rows'][0]['parsed_front']);
        $this->assertSame('foo', $result['rows'][1]['parsed_front']);
    }

    #[Test]
    public function given_25_lines_when_parse_then_preview_rows_capped_at_20(): void
    {
        // Arrange — 25 valid lines
        $lines = [];
        for ($i = 1; $i <= 25; $i++) {
            $lines[] = "front{$i}\tback{$i}";
        }
        $content = implode("\n", $lines);

        $result = $this->service->parseContent($content);

        $this->assertSame(25, $result['data_lines']);
        $this->assertSame(25, $result['valid_rows']);
        $this->assertCount(25, $result['rows']);
        $this->assertCount(20, $result['preview_rows']); // capped at 20
    }

    // ═══════════════════════════════════════════════════════════
    // B. parseLine() — Special Field Handling
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_sound_token_when_parse_then_extracts_audio(): void
    {
        $content = "hello\tworld\t[sound:audio.mp3]";

        $result = $this->service->parseContent($content);

        $this->assertSame('valid', $result['rows'][0]['status']);
        $this->assertSame('audio.mp3', $result['rows'][0]['parsed_audio_token']);
    }

    #[Test]
    public function given_img_tag_when_parse_then_filtered_out(): void
    {
        $content = "hello\tworld\t<img src=\"photo.jpg\">";

        $result = $this->service->parseContent($content);

        $this->assertSame('valid', $result['rows'][0]['status']);
        $this->assertSame('hello', $result['rows'][0]['parsed_front']);
        $this->assertSame('world', $result['rows'][0]['parsed_back']);
    }

    #[Test]
    public function given_tag_field_when_parse_then_extracted(): void
    {
        $content = "hello\tworld\ttags:vocabulary";

        $result = $this->service->parseContent($content);

        $this->assertSame('valid', $result['rows'][0]['status']);
        $this->assertSame('tags:vocabulary', $result['rows'][0]['parsed_tags']);
    }

    // ═══════════════════════════════════════════════════════════
    // C. toPlainText() — HTML Cleaning
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_html_content_when_toPlainText_then_strips_tags(): void
    {
        $result = $this->service->toPlainText('<b>hello</b> <i>world</i>');

        $this->assertSame('hello world', $result);
    }

    #[Test]
    public function given_nbsp_when_toPlainText_then_converts_to_space(): void
    {
        // \xc2\xa0 is UTF-8 encoded non-breaking space
        $result = $this->service->toPlainText("hello\xc2\xa0world");

        $this->assertSame('hello world', $result);
    }

    #[Test]
    public function given_html_entities_when_toPlainText_then_decodes(): void
    {
        $result = $this->service->toPlainText('caf&eacute; &amp; bar');

        $this->assertSame('café & bar', $result);
    }

    // ═══════════════════════════════════════════════════════════
    // D. normalizeForDuplicate()
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_mixed_content_when_normalizeForDuplicate_then_lowered_cleaned(): void
    {
        $result = $this->service->normalizeForDuplicate('<b>Hello!</b> World?');

        $this->assertSame('hello world', $result);
    }
}
