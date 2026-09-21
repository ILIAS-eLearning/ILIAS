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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;

final class XmlDeserializer implements Deserializer
{
    /** @var array<string, callable(array): void> */
    private array $handlers = [];

    private function __construct(private readonly \XMLReader $reader)
    {
    }

    public static function fromFile(string $path): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException("The file '{$path}' does not exist or is not readable.");
        }

        $reader = new \XMLReader();
        if (!$reader->open($path, null, LIBXML_NONET)) {
            throw new \RuntimeException("Unable to open XML file '{$path}'.");
        }

        return new self($reader);
    }

    public static function fromString(string $xml): self
    {
        $xml = trim($xml);
        $xml = preg_replace('/^\s*<\?xml[^>]*\?>\s*/i', '', $xml) ?? $xml;

        $reader = new \XMLReader();
        if (!$reader->XML("<deserializer-root>{$xml}</deserializer-root>", null, LIBXML_NONET)) {
            throw new \RuntimeException('Unable to open XML input.');
        }

        return new self($reader);
    }

    #[\Override]
    public function addHandler(string $group, callable $handler): void
    {
        $this->handlers[$group] = $handler;
    }

    #[\Override]
    public function process(): void
    {
        try {
            while ($this->reader->read()) {
                $node_name = $this->kebabToSnake($this->reader->name);

                if (
                    $this->reader->nodeType !== \XMLReader::ELEMENT
                    || !isset($this->handlers[$node_name])
                ) {
                    continue;
                }

                $this->handlers[$node_name]($this->readGroup());
            }
        } finally {
            $this->reader->close();
        }
    }

    /**
     * @return list<mixed>
     */
    private function readGroup(): array
    {
        if ($this->reader->isEmptyElement) {
            return [];
        }

        $group_depth = $this->reader->depth;
        $group_name = $this->reader->name;
        $group_data = [];

        while ($this->reader->read()) {
            if (
                $this->reader->nodeType === \XMLReader::END_ELEMENT
                && $this->reader->depth === $group_depth
                && $this->reader->name === $group_name
            ) {
                break;
            }

            if (
                $this->reader->nodeType !== \XMLReader::ELEMENT
                || $this->reader->depth !== $group_depth + 1
            ) {
                continue;
            }

            $group_data[] = $this->readElementValue();
        }

        return $group_data;
    }

    private function readElementValue(): mixed
    {
        $is_marked_empty_array = $this->isMarkedEmptyArray();

        if ($this->reader->isEmptyElement) {
            return $is_marked_empty_array ? [] : '';
        }

        $element_depth = $this->reader->depth;
        $element_name = $this->reader->name;
        $children = [];
        $text_content = '';

        while ($this->reader->read()) {
            if (
                $this->reader->nodeType === \XMLReader::END_ELEMENT
                && $this->reader->depth === $element_depth
                && $this->reader->name === $element_name
            ) {
                break;
            }

            if (
                $this->reader->nodeType === \XMLReader::ELEMENT
                && $this->reader->depth === $element_depth + 1
            ) {
                $child_key = $this->resolveElementKey();
                $child_value = $this->readElementValue();

                if ($child_key === null) {
                    $children[] = $child_value;
                    continue;
                }

                $this->appendValue($children, $child_key, $child_value);
                continue;
            }

            if (
                $this->reader->depth === $element_depth + 1
                && in_array(
                    $this->reader->nodeType,
                    [
                        \XMLReader::TEXT,
                        \XMLReader::CDATA,
                        \XMLReader::SIGNIFICANT_WHITESPACE
                    ],
                    true
                )
            ) {
                $text_content .= $this->reader->value;
            }
        }

        if ($children !== []) {
            return $children;
        }

        if ($is_marked_empty_array && trim($text_content) === '') {
            return [];
        }

        return $this->decodeScalarValue($text_content);
    }

    private function isMarkedEmptyArray(): bool
    {
        return $this->reader->getAttribute('type') === 'empty-array';
    }

    private function resolveElementKey(): ?string
    {
        if ($this->reader->name !== 'item') {
            return $this->kebabToSnake($this->reader->name);
        }

        $raw_key = $this->reader->getAttribute('key');
        if ($raw_key === null || $raw_key === '') {
            return null;
        }

        return $this->kebabToSnake($raw_key);
    }

    /**
     * @param array<array-key, mixed> $target
     */
    private function appendValue(array &$target, string $key, mixed $value): void
    {
        if (!array_key_exists($key, $target)) {
            $target[$key] = $value;
            return;
        }

        if (!is_array($target[$key]) || !array_is_list($target[$key])) {
            $target[$key] = [$target[$key]];
        }

        $target[$key][] = $value;
    }

    private function decodeScalarValue(string $value): mixed
    {
        $decoded = htmlspecialchars_decode($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
        return $decoded === 'NULL' ? null : $decoded;
    }

    private function kebabToSnake(string $name): string
    {
        return str_replace('-', '_', $name);
    }
}
