<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize\Serializer;

final class XmlSerializer implements Serializer
{
    private bool $has_document = false;
    private string $current_group = '';

    private function __construct(private readonly \XMLWriter $writer)
    {
    }

    public static function inMemory(): self
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);

        return new self($writer);
    }

    public function createDocument(string $comment): void
    {
        if ($this->has_document) {
            throw new \LogicException('XML document already started');
        }

        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->writeComment($comment);
        $this->has_document = true;
    }

    #[\Override]
    public function startGroup(string $name): void
    {
        $this->current_group = $name;
        $this->writer->startElement($this->formatName($name));
    }

    #[\Override]
    public function endGroup(string $name): void
    {
        if ($this->current_group !== $name) {
            throw new \LogicException("Group name mismatch: expected end of '{$this->current_group}', got '{$name}'");
        }

        $this->current_group = '';
        $this->writer->endElement();
    }

    #[\Override]
    public function group(string $name, callable $callback): void
    {
        $this->startGroup($name);
        $callback();
        $this->endGroup($name);
    }

    #[\Override]
    public function append(string $name, array $data): void
    {
        $this->writer->startElement($this->formatName($name));
        if ($data === []) {
            $this->writer->writeAttribute('type', 'empty-array');
        }

        $this->appendRecursive($data);
        $this->writer->endElement();
    }

    #[\Override]
    public function write(): string
    {
        if ($this->has_document) {
            $this->writer->endDocument();
        }

        return $this->writer->outputMemory(true);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function appendRecursive(array $data): void
    {
        foreach ($data as $key => $value) {
            $is_nested = is_array($value);
            $formatted_key = $this->formatName($key);

            if ($this->shouldUseItemElement($key, $formatted_key)) {
                $this->writer->startElement('item');

                if (!array_is_list($data)) {
                    $this->writer->writeAttribute('key', (string) $key);
                }
            } else {
                $this->writer->startElement($formatted_key);
            }

            if (!$is_nested) {
                $this->writer->writeRaw(match (gettype($value)) {
                    'NULL' => 'NULL',
                    'integer' => (string) $value,
                    'float' => (string) $value,
                    'boolean' => $value ? '1' : '0',
                    default => htmlspecialchars((string) $value),
                });
            } else {
                if ($value === []) {
                    $this->writer->writeAttribute('type', 'empty-array');
                }
                $this->appendRecursive($value);
            }

            $this->writer->endElement();
        }
    }

    private function shouldUseItemElement(int|string $key, string $formatted_key): bool
    {
        if (is_numeric($key) || str_contains((string) $key, '-') || $key === '') {
            return true;
        }

        return !$this->isValidXmlElementName($formatted_key);
    }

    private function isValidXmlElementName(string $name): bool
    {
        return $name !== '' && preg_match('/^[A-Za-z_][A-Za-z0-9._-]*$/', $name) === 1;
    }

    private function formatName(int|string $name): string
    {
        $output = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', str_replace(['_', ' '], '-', (string) $name)));
        return trim(preg_replace('/-+/', '-', $output), '-');
    }
}
