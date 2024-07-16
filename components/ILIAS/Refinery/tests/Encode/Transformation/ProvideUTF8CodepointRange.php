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

namespace ILIAS\Tests\Refinery\Encode\Transformation;

/**
 * Helper trait which provides a data provider for codepoint ranges where it is common wether or not a codepoint needs to be changed in order to preserve it's meaning when encdoding from one language to another.
 */
trait ProvideUTF8CodepointRange
{
    /**
     * Returns an array in a format that can be used for a data provider.
     * The purpose of this method is to provide a basic data provider to check the first byte range (255 entries) for common characters which are never encoded or which are always encoded among many languages.
     *
     * The returned data provider is designed to be fed to a test method of 3 parameters of types: string $exptected, string $input and string $assertionMethod.
     * For all alphanumeric characters in the range this method expects that the `input` and `expected` strings are the same additionally to all characters that should be ignored.
     * For all other characters it expects that the input character is not the same as the output character.
     *
     * @param string[] $ignore
     * @return array<string, {0: string, 1: string, 2: string}>
     */
    private static function oneByteRangeExcept(array $ignore = []): array
    {
        $byte_range = range(0, 0xFE);
        $set_names = array_map(fn(int $codepoint) => 'Codepoint: ' . $codepoint, $byte_range);

        return array_combine(
            $set_names,
            array_map(
                fn(string $chr, int $codepoint) => (
                    ctype_alnum($chr) || in_array($chr, $ignore, true) ?
                        self::asDataProviderArgs($chr, 'assertSame') :
                        self::asDataProviderArgs(self::isAscii($codepoint) ? $chr : self::twoByteChar($codepoint), 'assertNotSame')
                ),
                array_map(chr(...), $byte_range),
                $byte_range
            )
        );
    }

    private static function isAscii(int $codepoint): bool
    {
        return $codepoint <= 127;
    }

    /**
     * Convert a Unicode codepoint between 0x80 and 0x7FF to a valid UTF-8 character.
     * This is the range where unicode codepoints are encoded in UTF-8 with two bytes.
     */
    private static function twoByteChar(int $codepoint): string
    {
        return chr($codepoint >> 6 & 0x3f | 0xc0) . chr($codepoint & 0x3f | 0x80);
    }

    /**
     * Returns an entry for a dataset of a data provider for a test case with a signature of the form: string $exptected, string $input and string $assertionMethod.
     * @return {0: string, 1: string, 2: string}
     */
    private static function asDataProviderArgs(string $chr, string $method): array
    {
        return [$chr, $chr, $method];
    }
}
