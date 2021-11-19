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

        $i = 0;
        $stringParts = [];
        $matches = [];
        while (1 === \preg_match('@(^|[^[:alnum:]])(((https?://)|(www.))[^[:cntrl:][:space:]<>\'"]+)([^[:alnum:]]|$)@', \substr($maybeHTML, $i), $matches)) {
            $oldIndex = $i;
            $i += \strpos(\substr($maybeHTML, $i), $matches[0]);
            $stringParts[] = \substr($maybeHTML, $oldIndex, $i - $oldIndex);
            $i += \strlen($matches[1] . $matches[2]);
            if ($this->regexPos('@<a.*</a>@', \substr($maybeHTML, $i)) <= $this->regexPos('@</a>@', \substr($maybeHTML, $i))) {
                $maybeProtocol = '' === $matches[4] ? 'https://' : '';
                $stringParts[] = \sprintf('%s<a href="%s">%s</a>', $matches[1], $maybeProtocol . $matches[2], $matches[2], $matches[6]);
                continue;
            }
            $stringParts[] = $matches[1] . $matches[2];
        }

        $stringParts[] = \substr($maybeHTML, $i);

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
}
