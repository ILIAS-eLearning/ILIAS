<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ConstraintViolationException;

class MakeClickable implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    
    const PATTERN = '(^|[^[:alnum:]])(((https?:\/\/)|(www.))[^[:cntrl:][:space:]<>\'"]+)([^[:alnum:]]|$)';
    
    protected function determineParameters() : array
    {
        if (function_exists('mb_ereg')) {
            $matcher = 'mb_ereg';
            $pattern = self::PATTERN;
        } else {
            $matcher = 'preg_match';
            $pattern = '@' . self::PATTERN . '@';
        }
        return [$matcher, $pattern];
    }
    
    /**
     * @inheritDoc
     */
    public function transform($from) : string
    {
        $this->requireString($from);

        $endOfMatch = 0;
        $stringParts = [];
        $matches = [];
        [$matcher, $pattern] = $this->determineParameters();
    
        $checker = function () use ($matcher, $pattern, &$from, &$endOfMatch, &$matches) : bool {
            $r = call_user_func_array($matcher, [$pattern, substr($from, $endOfMatch), &$matches]);
            return $r === true || $r >>= 1; // since the return value differs in PHP7.4/8 and in preg_match/mb_ereg
        };
        
        while ($checker()) {
            $oldIndex = $endOfMatch;
            $endOfMatch += strpos(substr($from, $endOfMatch), $matches[0]);
            $stringParts[] = substr($from, $oldIndex, $endOfMatch - $oldIndex);
            $startOfMatch = $endOfMatch;
            $endOfMatch += strlen($matches[1] . $matches[2]);
            if ($this->shouldReplace($from, $startOfMatch, $endOfMatch)) {
                $maybeProtocol = ('' === $matches[4] || false === $matches[4]) ? 'https://' : '';
                $stringParts[] = sprintf(
                    '%s<a href="%s">%s</a>',
                    $matches[1],
                    $maybeProtocol . $matches[2],
                    $matches[2]
                );
                continue;
            }
            $stringParts[] = $matches[1] . $matches[2];
        }

        $stringParts[] = substr($from, $endOfMatch);

        return implode('', $stringParts);
    }

    private function regexPos(string $regexp, string $string) : int
    {
        $matches = [];
        if (1 === preg_match($regexp, $string, $matches)) {
            return strpos($string, $matches[0]);
        }

        return strlen($string);
    }

    /**
     * @param mixed $maybeHTML
     * @return void
     */
    private function requireString($maybeHTML) : void
    {
        if (!is_string($maybeHTML)) {
            throw new ConstraintViolationException('not a string', 'not_a_string');
        }
    }

    private function shouldReplace(string $maybeHTML, int $startOfMatch, int $endOfMatch) : bool
    {
        $isNotInAnchor = $this->regexPos('@<a.*</a>@', substr($maybeHTML, $endOfMatch)) <= $this->regexPos('@</a>@', substr($maybeHTML, $endOfMatch));
        $isNotATagAttribute = 0 === preg_match('/^[^>]*[[:space:]][[:alpha:]]+</', strrev(substr($maybeHTML, 0, $startOfMatch)));

        return $isNotInAnchor && $isNotATagAttribute;
    }
}
