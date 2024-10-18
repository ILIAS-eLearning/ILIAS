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

class PostingReplySubjectBuilder
{
    private const EXPECTED_REPLY_PREFIX_END = ':';
    private const EXPECTED_NUMBER_WRAPPER_CHAR_START_PATTERN = '\\(';
    private const EXPECTED_NUMBER_WRAPPER_CHAR_END_PATTERN = '\\)';
    private const EXPECTED_NUMBER_WRAPPER_CHAR_START = '(';
    private const EXPECTED_NUMBER_WRAPPER_CHAR_END = ')';

    /** @var null|array{"strpos": \Closure(string, string, ?int=): (int|false), "strrpos": \Closure(string, string, ?int=): (int|false), "strlen": \Closure(string): int, "substr": \Closure(string, int, ?int=): string} */
    private static ?array $f = null;

    private string $reply_prefix;
    private string $repeated_reply_prefix;

    public function __construct(string $reply_prefix, string $repeated_reply_prefix)
    {
        $this->reply_prefix = trim($reply_prefix);
        $this->repeated_reply_prefix = trim($repeated_reply_prefix);

        if (self::$f === null) {
            self::$f = [
                'strpos' => function (string $haystack, string $needle, ?int $offset = 0) {
                    return function_exists('mb_strpos') ? mb_strpos($haystack, $needle, $offset, 'UTF-8') : strpos(
                        $haystack,
                        $needle,
                        $offset
                    );
                },
                'strrpos' => function (string $haystack, string $needle, ?int $offset = 0) {
                    return function_exists('mb_strrpos') ? mb_strrpos($haystack, $needle, $offset, 'UTF-8') : strrpos(
                        $haystack,
                        $needle,
                        $offset
                    );
                },
                'strlen' => function (string $string): int {
                    return function_exists('mb_strlen') ? mb_strlen($string, 'UTF-8') : strlen($string);
                },
                'substr' => function (string $string, int $start, ?int $length = null): string {
                    return function_exists('mb_substr') ? mb_substr($string, $start, $length, 'UTF-8') : substr(
                        $string,
                        $start,
                        $length
                    );
                }
            ];
        }
    }

    private function handleSubjectWithoutReplyPrefixOrRepeatedReplyPrefix(
        string $subj_of_parent_posting,
        string $effective_reply_prefix,
        string $effective_optimized_repeated_reply_prefix
    ): string {
        $final_subject = $subj_of_parent_posting;

        $reply_prefix_start = (self::$f['substr'])(
            $effective_reply_prefix,
            0,
            -(self::$f['strlen'])(self::EXPECTED_REPLY_PREFIX_END)
        );

        $repeated_reply_prefix_regex = implode('', [
            '/^',
            '(' . preg_quote($reply_prefix_start, '/') . '\s*' . self::EXPECTED_REPLY_PREFIX_END . '\s*)+',
            '/'
        ]);

        $matches = null;
        preg_match($repeated_reply_prefix_regex, $subj_of_parent_posting, $matches);
        $number_of_repetitions = isset($matches[0]) ?
            preg_match_all(
                '/' . preg_quote($reply_prefix_start, '/') . '\s*' . self::EXPECTED_REPLY_PREFIX_END . '\s*/',
                $matches[0]
            ) : 0;

        if ($number_of_repetitions >= 1) {
            // i.e. $final_subject = "Re: Re: Re: ... " -> "Re(4):"
            $number_of_repetitions++;
            $final_subject = sprintf(
                $effective_optimized_repeated_reply_prefix,
                $number_of_repetitions
            ) . ' ' . trim(str_replace($matches[0], '', $subj_of_parent_posting));
        } elseif ($number_of_repetitions === 0) {
            // the first reply to a thread
            $final_subject = $effective_reply_prefix . ' ' . $subj_of_parent_posting;
        }

        return $final_subject;
    }

    private function handleSubjectStartsWithOptimizedRepetitionReplyPattern(string $subject): string
    {
        $final_subject = $subject;

        $wrapper_start_pos = (self::$f['strpos'])($subject, self::EXPECTED_NUMBER_WRAPPER_CHAR_START);
        $wrapper_end_pos = (self::$f['strpos'])($subject, self::EXPECTED_NUMBER_WRAPPER_CHAR_END);

        if ($wrapper_start_pos === false || $wrapper_end_pos === false || $wrapper_end_pos < $wrapper_start_pos) {
            return $final_subject;
        }

        $length = $wrapper_end_pos - $wrapper_start_pos;
        $wrapper_start_pos++;

        $txt_num_replies = (self::$f['substr'])($subject, $wrapper_start_pos, $length - 1);
        if (is_numeric($txt_num_replies) && $txt_num_replies > 0) {
            $number_of_replies = ((int) trim($txt_num_replies)) + 1;
            $final_subject = (self::$f['substr'])(
                $subject,
                0,
                $wrapper_start_pos
            ) . $number_of_replies . (self::$f['substr'])(
                $subject,
                $wrapper_end_pos
            );
        }

        return $final_subject;
    }

    public function build(string $subj_of_parent_posting): string
    {
        $subj_of_parent_posting = trim($subj_of_parent_posting);
        $final_subject = '';

        $reply_prefix = $this->reply_prefix;
        if ((self::$f['substr'])(
            $reply_prefix,
            -((self::$f['strlen'])(self::EXPECTED_REPLY_PREFIX_END))
        ) !== self::EXPECTED_REPLY_PREFIX_END) {
            $reply_prefix .= self::EXPECTED_REPLY_PREFIX_END;
        }

        $effective_optimized_repeated_reply_prefix = $this->repeated_reply_prefix;
        if ((self::$f['substr'])(
            $effective_optimized_repeated_reply_prefix,
            -((self::$f['strlen'])(self::EXPECTED_REPLY_PREFIX_END))
        ) !== self::EXPECTED_REPLY_PREFIX_END) {
            $effective_optimized_repeated_reply_prefix .= self::EXPECTED_REPLY_PREFIX_END;
        }

        $optimized_repeated_reply_prefix_start = substr_replace(
            $reply_prefix,
            self::EXPECTED_NUMBER_WRAPPER_CHAR_START,
            (self::$f['strrpos'])($reply_prefix, self::EXPECTED_REPLY_PREFIX_END),
            (self::$f['strlen'])(self::EXPECTED_REPLY_PREFIX_END)
        );

        $optimized_repeated_reply_prefix_begin_pattern = preg_quote(
            (self::$f['substr'])(
                $optimized_repeated_reply_prefix_start,
                0,
                (self::$f['strrpos'])(
                    $optimized_repeated_reply_prefix_start,
                    self::EXPECTED_NUMBER_WRAPPER_CHAR_START
                )
            ),
            '/'
        );

        $optimized_repeated_reply_prefix_regex = implode('', [
            '/^',
            $optimized_repeated_reply_prefix_begin_pattern,
            '\s*?' . self::EXPECTED_NUMBER_WRAPPER_CHAR_START_PATTERN . '\s*?\d+\s*?' . self::EXPECTED_NUMBER_WRAPPER_CHAR_END_PATTERN,
            '/'
        ]);

        if (preg_match($optimized_repeated_reply_prefix_regex, $subj_of_parent_posting)) {
            // i.e. $subj_of_parent_posting = "Re(12):" or "Re (12):"
            $final_subject = $this->handleSubjectStartsWithOptimizedRepetitionReplyPattern($subj_of_parent_posting);
        } else {
            // i.e. $subj_of_parent_posting = "Re: Re: Re: ..."
            $final_subject = $this->handleSubjectWithoutReplyPrefixOrRepeatedReplyPrefix(
                $subj_of_parent_posting,
                $reply_prefix,
                $effective_optimized_repeated_reply_prefix
            );
        }

        return $final_subject;
    }
}
