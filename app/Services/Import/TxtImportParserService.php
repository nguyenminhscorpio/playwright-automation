<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TxtImportParserService
{
    public function parseUploadedFile(UploadedFile $file): array
    {
        $content = $file->get();

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_hash' => hash('sha256', $content),
            'detected_format' => 'anki_txt_tab',
            ...$this->parseContent($content),
        ];
    }

    public function parseContent(string $content): array
    {
        $normalizedContent = str_replace(["\r\n", "\r"], "\n", $content);
        $allLines = explode("\n", $normalizedContent);

        $rows = [];
        $dataLines = 0;
        $validRows = 0;
        $invalidRows = 0;

        foreach ($allLines as $index => $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '' || Str::startsWith($trimmedLine, '#')) {
                continue;
            }

            $dataLines++;
            $row = $this->parseLine($index + 1, $line);
            $rows[] = $row;

            if ($row['status'] === 'valid') {
                $validRows++;
            } else {
                $invalidRows++;
            }
        }

        return [
            'total_lines' => count($allLines),
            'data_lines' => $dataLines,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'rows' => $rows,
            'preview_rows' => array_slice(
                array_map(
                    static fn (array $row): array => [
                        'row_number' => $row['row_number'],
                        'front_text' => $row['parsed_front'],
                        'back_text' => $row['parsed_back'],
                        'status' => $row['status'],
                        'error_message' => $row['error_message'],
                    ],
                    $rows
                ),
                0,
                20
            ),
        ];
    }

    public function toPlainText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace("\xc2\xa0", ' ', $decoded);
        $decoded = strip_tags($decoded);
        $decoded = preg_replace('/\s+/u', ' ', $decoded) ?? $decoded;

        return trim($decoded);
    }

    public function normalizeForDuplicate(string $value): string
    {
        $plain = mb_strtolower($this->toPlainText($value));
        $plain = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $plain) ?? $plain;
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;

        return trim($plain);
    }

    private function parseLine(int $rowNumber, string $line): array
    {
        $rawFields = preg_split("/\t/u", $line) ?: [];
        $textFields = [];
        $audioTokens = [];
        $tags = [];

        foreach ($rawFields as $field) {
            $trimmedField = trim($field);

            if ($trimmedField === '') {
                continue;
            }

            if (preg_match('/^\[sound:(.+)\]$/iu', $trimmedField, $matches) === 1) {
                $audioTokens[] = $matches[1];
                continue;
            }

            if (preg_match('/<img\b/iu', $trimmedField) === 1) {
                continue;
            }

            if ($this->looksLikeTagField($trimmedField)) {
                $tags[] = $trimmedField;
                continue;
            }

            $cleanedField = $this->toPlainText($trimmedField);

            if ($cleanedField !== '') {
                $textFields[] = $cleanedField;
            }
        }

        if (count($textFields) < 2) {
            return [
                'row_number' => $rowNumber,
                'raw_content' => $line,
                'parsed_front' => null,
                'parsed_back' => null,
                'parsed_audio_token' => $audioTokens === [] ? null : implode('|', $audioTokens),
                'parsed_tags' => $tags === [] ? null : implode('|', $tags),
                'status' => 'invalid',
                'error_message' => 'Row must contain at least 2 text fields after filtering.',
            ];
        }

        return [
            'row_number' => $rowNumber,
            'raw_content' => $line,
            'parsed_front' => array_shift($textFields),
            'parsed_back' => implode(' ', $textFields),
            'parsed_audio_token' => $audioTokens === [] ? null : implode('|', $audioTokens),
            'parsed_tags' => $tags === [] ? null : implode('|', $tags),
            'status' => 'valid',
            'error_message' => null,
        ];
    }

    private function looksLikeTagField(string $value): bool
    {
        if (preg_match('/^(tags?|deck|note-type):/iu', $value) === 1) {
            return true;
        }

        return false;
    }
}
