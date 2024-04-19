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

namespace ILIAS\Refinery\Encode\Transformation;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ValueError;

/**
 * Inspired by: Laminas escaper: https://github.com/laminas/laminas-escaper.
 * This class expects a valid UTF-8 string. Conversion between encodings is not the responsibility of this class.
 *
 * This class encodes a given string so it can be used as a value inside a HTML attribute tag (e.g. <div class="ENCODED_VALUE"></div>).
 * The given string is encoded so it can be used in any HTML Tag regardless if the value is enclosed in double quotes ("), single quotes (') or no quotes at all (e.g. <div class=ENCODED_VALUE></div>).
 * Please see for more information of the syntax of HTML attributes: https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
 * Please see https://html.spec.whatwg.org/multipage/dom.html#phrasing-content for a list of all allowed characters and escape sequences.
 */
class HTMLAttributeValue implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    public function transform($from)
    {
        return $this->encode($from);
    }

    private function encode(string $from): string
    {
        return preg_replace_callback(
            '/[^a-z0-9,._-]/iSu',
            fn(array $m): string => $this->replace($m[0]),
            $from
        ) ?? throw new ValueError('Invalid UTF-8 string given.');
    }

    private function replace(string $utf8_char): string
    {
        $codepoint = $this->utf8CharacterToCodepoint($utf8_char);

        // All unicode control characters besides white space codepoints as well as noncharacters are not allowed in HTML attributes.
        if ($this->isNonPrintableControl($utf8_char, $codepoint) || $this->isNonCharacter($codepoint)) {
            return '&#xFFFD;'; // Unicode "replacement character", indicating a non printable character.
        }

        return match ($codepoint) {
            34 => '&quot;',
            38 => '&amp;',
            60 => '&lt;',
            62 => '&gt;',
            default => sprintf($codepoint > 255 ? '&#x%04X;' : '&#x%02X;', $codepoint),
        };
    }

    /**
     * Decodes a given UTF-8 character (which may be multibyte) to a Unicode codepoint.
     * E.g. The multibyte character "€", encoded in UTF-8 as the byte sequence 0xE2 0x82 0xAC,
     * will return the integer 0x20AC, which is the corresponding unicode codepoint.
     */
    private function utf8CharacterToCodepoint(string $utf8_char): int
    {
        // UTF-32 encodes Unicode codepoints as itself. BE stands for Big Endian.
        return hexdec(bin2hex(strlen($utf8_char) > 1 ? (mb_convert_encoding($utf8_char, 'UTF-32BE', 'UTF-8')) : $utf8_char));
    }

    /**
     * The unicode range for control codepoints is from U+0000 NULL to U+001F (inclusive) and from U+007F to U+009F (inclusive).
     * The range U+0000 NULL to U+001F and the codepoint U+007F are all one byte long and are covered by the PHP function ctype_cntrl.
     *
     * See https://infra.spec.whatwg.org/#control for more information.
     */
    private function isNonPrintableControl(string $utf8_char, int $codepoint): bool
    {
        return strlen($utf8_char) === 1 ?
            ctype_cntrl($utf8_char) && !ctype_space($utf8_char) :
            $codepoint <= 0x9F;
    }

    /**
     * Unicode specifies a fixed list of 66 codepoints to be "noncharacters".
     * Please see https://infra.spec.whatwg.org/#noncharacter for the complete list.
     */
    private function isNonCharacter(int $codepoint): bool
    {
        return 0xFDD0 <= $codepoint && $codepoint <= 0xFDEF ||
               in_array($codepoint, [0xFFFE, 0xFFFF, 0x1FFFE, 0x1FFFF, 0x2FFFE, 0x2FFFF, 0x3FFFE, 0x3FFFF, 0x4FFFE, 0x4FFFF, 0x5FFFE, 0x5FFFF, 0x6FFFE, 0x6FFFF, 0x7FFFE, 0x7FFFF, 0x8FFFE, 0x8FFFF, 0x9FFFE, 0x9FFFF, 0xAFFFE, 0xAFFFF, 0xBFFFE, 0xBFFFF, 0xCFFFE, 0xCFFFF, 0xDFFFE, 0xDFFFF, 0xEFFFE, 0xEFFFF, 0xFFFFE, 0xFFFFF, 0x10FFFE,  0x10FFFF], true);
    }
}
