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

/**
 * Shared XMLWriter-based parsing logic used by both in-memory and file-based XML serializers.
 */
trait XMLDeserializerTrait
{
    /** @var array<string, callable(array): void> */
    private array $handler = [];

    public function addHandler(string $group, callable $handler): void
    {
        $this->handler[$group] = $handler;
    }

    private function processReader(\XMLReader $reader): void
    {
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
                continue;
            }

            $group_data[] = $this->readElementValue($reader);
        }

        return $group_data;
    }

    private function readElementValue(\XMLReader $reader): mixed
    {
        $is_marked_empty_array = $this->isMarkedEmptyArray($reader);

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
                $child_key = $this->resolveElementKey($reader);
                $child_value = $this->readElementValue($reader);

                if ($child_key === null) {
                    $children[] = $child_value;
                    continue;
                }

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
                $text_content .= $reader->value;
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

    private function isMarkedEmptyArray(\XMLReader $reader): bool
    {
        return $reader->getAttribute('type') === 'empty-array';
    }

    private function resolveElementKey(\XMLReader $reader): ?string
    {
        if ($reader->name !== 'item') {
            return $this->kebabToSnake($reader->name);
        }

        $raw_key = $reader->getAttribute('key');
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
