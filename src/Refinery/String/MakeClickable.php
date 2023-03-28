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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ConstraintViolationException;
use Closure;

class MakeClickable implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private const URL_PATTERN = '(^|[^[:alnum:]])(((https?:\/\/)|(www.))[^[:cntrl:][:space:]<>\'"]+)([^[:alnum:]]|$)';

    private bool $open_in_new_tab;

    public function __construct($open_in_new_tab = true)
    {
        $this->open_in_new_tab = $open_in_new_tab;
    }

    public function transform($from): string
    {
        $this->requireString($from);

        return $this->replaceMatches($from, fn (int $startOfMatch, int $endOfMatch, string $url, string $protocol): string => (
            $this->shouldReplace($from, $startOfMatch, $endOfMatch) ?
                $this->replace($url, $protocol) :
                $url
        ));
    }

    private function replaceMatches(string $from, callable $replace): string
    {
        $endOfLastMatch = 0;
        $stringParts = [];

        while (null !== ($matches = $this->match(self::URL_PATTERN, substr($from, $endOfLastMatch)))) {
            $startOfMatch = $endOfLastMatch + strpos(substr($from, $endOfLastMatch), $matches[0]);
            $endOfMatch   = $startOfMatch   + strlen($matches[1] . $matches[2]);

            $stringParts[] = substr($from, $endOfLastMatch, $startOfMatch - $endOfLastMatch);
            $stringParts[] = $matches[1] . $replace($startOfMatch, $endOfMatch, $matches[2], $matches[4]);

            $endOfLastMatch = $endOfMatch;
        }

        $stringParts[] = substr($from, $endOfLastMatch);

        return implode('', $stringParts);
    }

    private function regexPos(string $regexp, string $string): int
    {
        $matches = $this->match($regexp, $string);
        if (null !== $matches) {
            return strpos($string, $matches[0]);
        }

        return strlen($string);
    }

    /**
     * @param mixed $maybeHTML
     * @return void
     */
    private function requireString($maybeHTML): void
    {
        if (!is_string($maybeHTML)) {
            throw new ConstraintViolationException('not a string', 'not_a_string');
        }
    }

    private function shouldReplace(string $maybeHTML, int $startOfMatch, int $endOfMatch): bool
    {
        $isNotInAnchor = $this->regexPos('<a.*</a>', substr($maybeHTML, $endOfMatch)) <= $this->regexPos('</a>', substr($maybeHTML, $endOfMatch));
        $isNotATagAttribute = null === $this->match('^[^>]*[[:space:]][[:alpha:]]+<', strrev(substr($maybeHTML, 0, $startOfMatch)));

        return $isNotInAnchor && $isNotATagAttribute;
    }

    /**
     * @param string $pattern Pattern without delimiters.
     * @return null|string[]
     */
    private function match(string $pattern, string $haystack): ?array
    {
        $pattern = str_replace('@', '\@', $pattern);
        return 1 === preg_match('@' . $pattern . '@', $haystack, $matches) ? $matches : null;
    }

    private function replace(string $url, string $protocol): string
    {
        $maybeProtocol = !$protocol ? 'https://' : '';
        return sprintf(
            '<a%s href="%s">%s</a>',
            $this->additionalAttributes(),
            $maybeProtocol . $url,
            $url
        );
    }

    protected function additionalAttributes(): string
    {
        if ($this->open_in_new_tab) {
            return ' target="_blank" rel="noopener"';
        }

        return '';
    }
}
