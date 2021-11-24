<?php declare(strict_types=1);

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ConstraintViolationException;

class MakeClickable implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @return string
     */
    public function transform($maybeHTML)
    {
        $this->requireString($maybeHTML);

        $endOfMatch = 0;
        $stringParts = [];
        $matches = [];
        while (1 === \preg_match('@(^|[^[:alnum:]])(((https?://)|(www.))[^[:cntrl:][:space:]<>\'"]+)([^[:alnum:]]|$)@', \substr($maybeHTML, $endOfMatch), $matches)) {
            $oldIndex = $endOfMatch;
            $endOfMatch += \strpos(\substr($maybeHTML, $endOfMatch), $matches[0]);
            $stringParts[] = \substr($maybeHTML, $oldIndex, $endOfMatch - $oldIndex);
            $startOfMatch = $endOfMatch;
            $endOfMatch += \strlen($matches[1] . $matches[2]);
            if ($this->shouldReplace($maybeHTML, $startOfMatch, $endOfMatch)) {
                $maybeProtocol = '' === $matches[4] ? 'https://' : '';
                $stringParts[] = \sprintf('%s<a href="%s">%s</a>', $matches[1], $maybeProtocol . $matches[2], $matches[2], $matches[6]);
                continue;
            }
            $stringParts[] = $matches[1] . $matches[2];
        }

        $stringParts[] = \substr($maybeHTML, $endOfMatch);

        return \join('', $stringParts);
    }

    private function regexPos(string $regexp, string $string) : int
    {
        $matches = [];
        if (1 === \preg_match($regexp, $string, $matches)) {
            return \strpos($string, $matches[0]);
        }

        return \strlen($string);
    }

    private function requireString($maybeHTML) : void
    {
        if (!\is_string($maybeHTML)) {
            throw new ConstraintViolationException('not a string', 'not_a_string');
        }
    }

    private function shouldReplace(string $maybeHTML, int $startOfMatch, int $endOfMatch) : bool
    {
        $isNotInAnchor = $this->regexPos('@<a.*</a>@', \substr($maybeHTML, $endOfMatch)) <= $this->regexPos('@</a>@', \substr($maybeHTML, $endOfMatch));
        $isNotATagAttribute = 0 === \preg_match('/^[^>]*[[:space:]][[:alpha:]]+</', \strrev(\substr($maybeHTML, 0, $startOfMatch)));

        return $isNotInAnchor && $isNotATagAttribute;
    }
}
