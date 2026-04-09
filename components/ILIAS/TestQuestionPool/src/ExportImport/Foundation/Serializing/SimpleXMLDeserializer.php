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

/**
 * Simple XML deserializer that reads an XML string in chunks and invokes registered handlers per group.
 */
class SimpleXMLDeserializer implements Deserializer
{
    private string $xml = '';

    /** @var array<string, callable(array): void> $handler */
    private array $handler = [];

    /**
     * @inheritDoc
     */
    public function open(string $path): static
    {
        $clone = clone $this;
        $clone->xml = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function addHandler(string $group, callable $handler): void
    {
        $this->handler[$group] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        $reader = new \XMLReader();
        $xml = $this->prepareXmlInput($this->xml);

        if (!$reader->XML($xml, null, LIBXML_NONET)) {
            throw new \RuntimeException('Unable to read XML input.');
        }

        while ($reader->read()) {
            $node_name = $this->kebabToSnake($reader->name);

            if ($reader->nodeType !== \XMLReader::ELEMENT || !isset($this->handler[$node_name])) {
                continue;
            }

            $group_data = $this->readGroup($reader);
            $this->handler[$node_name]($group_data);
        }

        $reader->close();
    }

    /**
     * @return list<mixed>
     */
    private function readGroup(\XMLReader $reader): array
    {
        if ($reader->isEmptyElement) {
            return [];
        }

        // Track the current group boundary so we can stop exactly at its closing tag
        $group_depth = $reader->depth;
        $group_name = $reader->name;
        $group_data = [];

        while ($reader->read()) {
            if (
                $reader->nodeType === \XMLReader::END_ELEMENT
                && $reader->depth === $group_depth
                && $reader->name === $group_name
            ) {
                break;
            }

            if (
                $reader->nodeType !== \XMLReader::ELEMENT
                || $reader->depth !== $group_depth + 1
            ) {
                // Only direct children belong to this group entry list
                continue;
            }

            $group_data[] = $this->readElementValue($reader);
        }

        return $group_data;
    }

    private function readElementValue(\XMLReader $reader): mixed
    {
        $is_marked_empty_array = $this->isMarkedEmptyArray($reader);

        // Keep empty elements as empty strings to preserve the legacy XML shape
        if ($reader->isEmptyElement) {
            if ($is_marked_empty_array) {
                return [];
            }
            return '';
        }

        $element_depth = $reader->depth;
        $element_name = $reader->name;
        $children = [];
        $text_content = '';

        while ($reader->read()) {
            if (
                $reader->nodeType === \XMLReader::END_ELEMENT
                && $reader->depth === $element_depth
                && $reader->name === $element_name
            ) {
                break;
            }

            if (
                $reader->nodeType === \XMLReader::ELEMENT
                && $reader->depth === $element_depth + 1
            ) {
                // Direct child elements are deserialized recursively
                $child_key = $this->resolveElementKey($reader);
                $child_value = $this->readElementValue($reader);

                if ($child_key === null) {
                    // Unkeyed <item> nodes are appended as list entries
                    $children[] = $child_value;
                    continue;
                }

                // Repeated keys are normalized to list values via appendValue()
                $this->appendValue($children, $child_key, $child_value);
                continue;
            }

            if (
                $reader->depth === $element_depth + 1
                && in_array(
                    $reader->nodeType,
                    [
                        \XMLReader::TEXT,
                        \XMLReader::CDATA,
                        \XMLReader::SIGNIFICANT_WHITESPACE
                    ],
                    true
                )
            ) {
                // Keep raw text content and decode scalar tokens after traversal
                $text_content .= $reader->value;
            }
        }

        if ($children !== []) {
            // Structured child data has precedence over accumulated text content
            return $children;
        }

        if ($is_marked_empty_array && trim($text_content) === '') {
            return [];
        }

        return $this->decodeScalarValue($text_content);
    }

    private function isMarkedEmptyArray(\XMLReader $reader): bool
    {
        return $reader->getAttribute('type') === 'empty-array';
    }

    private function resolveElementKey(\XMLReader $reader): ?string
    {
        // Regular element names map directly to associative keys
        if ($reader->name !== 'item') {
            return $this->kebabToSnake($reader->name);
        }

        // <item> nodes are list-like unless they define an explicit key attribute
        $raw_key = $reader->getAttribute('key');
        if ($raw_key === null || $raw_key === '') {
            return null;
        }

        // Normalize kebab-case keys to snake_case for downstream consumers
        return $this->kebabToSnake($raw_key);
    }

    /**
     * @param array<array-key, mixed> $target
     * @param mixed $value
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

    private function prepareXmlInput(string $xml): string
    {
        $xml = preg_replace('/^\s*<\?xml[^>]*\?>\s*/i', '', trim($xml)) ?? trim($xml);
        return "<deserializer-root>{$xml}</deserializer-root>";
    }
}
