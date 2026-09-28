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

namespace ILIAS\COPage\WordDiff;

/**
 * Creates word-level marked-up versions of two text sequences.
 */
class WordDiff
{
    private const MAX_LINE_LENGTH = 10000;

    /** @var list<string> */
    private array $original;

    /** @var list<string> */
    private array $closing;

    /**
     * @param list<string> $original_lines
     * @param list<string> $closing_lines
     */
    public function __construct(array $original_lines, array $closing_lines)
    {
        [$original_words, $original_comparison] = $this->split($original_lines);
        [$closing_words, $closing_comparison] = $this->split($closing_lines);

        $matches = $this->findMatches($original_comparison, $closing_comparison);
        $original_tokens = [];
        $original_tags = [];
        $closing_tokens = [];
        $closing_tags = [];
        $original_position = 0;
        $closing_position = 0;

        foreach ($matches as [$original_match, $closing_match]) {
            $this->appendChangedTokens(
                array_slice($original_words, $original_position, $original_match - $original_position),
                'del',
                $original_tokens,
                $original_tags
            );
            $this->appendChangedTokens(
                array_slice($closing_words, $closing_position, $closing_match - $closing_position),
                'ins',
                $closing_tokens,
                $closing_tags
            );

            $original_tokens[] = $original_words[$original_match];
            $original_tags[] = '';
            $closing_tokens[] = $closing_words[$closing_match];
            $closing_tags[] = '';
            $original_position = $original_match + 1;
            $closing_position = $closing_match + 1;
        }

        $this->appendChangedTokens(
            array_slice($original_words, $original_position),
            'del',
            $original_tokens,
            $original_tags
        );
        $this->appendChangedTokens(
            array_slice($closing_words, $closing_position),
            'ins',
            $closing_tokens,
            $closing_tags
        );

        $this->original = $this->render($original_tokens, $original_tags);
        $this->closing = $this->render($closing_tokens, $closing_tags);
    }

    /** @return list<string> */
    public function orig(): array
    {
        return $this->original;
    }

    /** @return list<string> */
    public function closing(): array
    {
        return $this->closing;
    }

    /**
     * @param list<string> $lines
     * @return array{list<string>, list<string>}
     */
    private function split(array $lines): array
    {
        $words = [];
        $comparison = [];

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $words[] = "\n";
                $comparison[] = "\n";
            }

            if (strlen($line) > self::MAX_LINE_LENGTH) {
                $words[] = $line;
                $comparison[] = $line;
                continue;
            }

            $matches = [];
            if (preg_match_all(
                '/ ( [^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
                $line,
                $matches
            )) {
                array_push($words, ...$matches[0]);
                array_push($comparison, ...$matches[1]);
            }
        }

        return [$words, $comparison];
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     * @return list<array{int, int}>
     */
    private function findMatches(array $left, array $right): array
    {
        if ($left === $right) {
            $matches = [];
            foreach ($left as $index => $_word) {
                $matches[] = [$index, $index];
            }
            return $matches;
        }

        return $this->findMatchesInRange($left, $right, 0, 0);
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     * @return list<array{int, int}>
     */
    private function findMatchesInRange(
        array $left,
        array $right,
        int $left_offset,
        int $right_offset
    ): array {
        if ($left === [] || $right === []) {
            return [];
        }

        if (count($left) === 1) {
            $position = array_search($left[0], $right, true);
            return $position === false ? [] : [[$left_offset, $right_offset + $position]];
        }

        $middle = intdiv(count($left), 2);
        $left_half = array_slice($left, 0, $middle);
        $right_half = array_slice($left, $middle);
        $forward_lengths = $this->lcsLengths($left_half, $right);
        $backward_lengths = $this->lcsLengths(array_reverse($right_half), array_reverse($right));

        $right_split = 0;
        $longest = -1;
        $right_length = count($right);
        for ($index = 0; $index <= $right_length; $index++) {
            $length = $forward_lengths[$index] + $backward_lengths[$right_length - $index];
            if ($length > $longest) {
                $longest = $length;
                $right_split = $index;
            }
        }

        return array_merge(
            $this->findMatchesInRange($left_half, array_slice($right, 0, $right_split), $left_offset, $right_offset),
            $this->findMatchesInRange(
                $right_half,
                array_slice($right, $right_split),
                $left_offset + $middle,
                $right_offset + $right_split
            )
        );
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     * @return list<int>
     */
    private function lcsLengths(array $left, array $right): array
    {
        $previous = array_fill(0, count($right) + 1, 0);
        foreach ($left as $left_word) {
            $current = [0];
            foreach ($right as $index => $right_word) {
                $current[] = $left_word === $right_word
                    ? $previous[$index] + 1
                    : max($previous[$index + 1], $current[$index]);
            }
            $previous = $current;
        }

        return $previous;
    }

    /**
     * @param list<string> $tokens
     * @param list<string> $output_tokens
     * @param list<string> $output_tags
     */
    private function appendChangedTokens(
        array $tokens,
        string $tag,
        array &$output_tokens,
        array &$output_tags
    ): void {
        foreach ($tokens as $token) {
            $output_tokens[] = $token;
            $output_tags[] = $tag;
        }
    }

    /**
     * @param list<string> $tokens
     * @param list<string> $tags
     * @return list<string>
     */
    private function render(array $tokens, array $tags): array
    {
        $lines = [];
        $line = '';
        $group = '';
        $current_tag = '';

        foreach ($tokens as $index => $token) {
            $tag = $tags[$index];
            if ($tag !== $current_tag) {
                $this->flushGroup($line, $group, $current_tag);
                $current_tag = $tag;
            }

            if ($token !== '' && $token[0] === "\n") {
                $this->flushGroup($line, $group, $current_tag);
                $lines[] = $line !== '' ? $line : '&#160;';
                $line = '';
                $token = substr($token, 1);
            }
            $group .= $token;
        }

        $this->flushGroup($line, $group, $current_tag);
        $lines[] = $line !== '' ? $line : '&#160;';
        return $lines;
    }

    private function flushGroup(string &$line, string &$group, string $tag): void
    {
        if ($group !== '') {
            if ($tag === 'ins') {
                $line .= '[ilDiffInsStart]' . htmlspecialchars($group) . '[ilDiffInsEnd]';
            } elseif ($tag === 'del') {
                $line .= '[ilDiffDelStart]' . htmlspecialchars($group) . '[ilDiffDelEnd]';
            } else {
                $line .= htmlspecialchars($group);
            }
        }
        $group = '';
    }
}
