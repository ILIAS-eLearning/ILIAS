<?php

declare(strict_types=1);

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

namespace ILIAS\Data;

use ILIAS\Data\LanguageTag\Standard;
use ILIAS\Data\LanguageTag\Regular;
use ILIAS\Data\LanguageTag\Irregular;
use ILIAS\Data\LanguageTag\PrivateUse;
use InvalidArgumentException;
use ILIAS\Refinery\Custom\Transformation as Custom;
use ILIAS\Refinery\Parser\ABNF\Brick;
use ILIAS\Data\Result\Error;
use Closure;

/**
 * This class represents a valid language tag that should be used instead of plain strings.
 * The language tag is validated by the provided language tag definition.
 *
 * RFC 5646 compliant language tag definition (https://www.ietf.org/rfc/bcp/bcp47.txt).
 */
abstract class LanguageTag
{
    abstract public function value(): string;

    public function __toString(): string
    {
        return $this->value();
    }

    public static function fromString(string $string): static
    {
        $brick = new Brick();
        return $brick->apply(self::definition($brick), $string)->except(static fn (): Result => (
            new Error('Given string is no valid language tag.')
        ))->value();
    }

    /**
     * This definition is directly translated from the ABNF definition on https://www.ietf.org/rfc/bcp/bcp47.txt.
     */
    private static function definition(Brick $brick): Closure
    {
        $extlang = $brick->sequence([
            $brick->repeat(3, 3, $brick->alpha()),
            $brick->repeat(0, 2, $brick->sequence(['-', $brick->repeat(3, 3, $brick->alpha())])),
        ]);

        $language = $brick->either([
            $brick->sequence([
                'lang' => $brick->repeat(2, 3, $brick->alpha()),
                $brick->repeat(0, 1, $brick->sequence(['-', 'extlang' => $extlang])),
            ]),
            $brick->sequence(['lang' => $brick->repeat(4, 4, $brick->alpha())]),
            $brick->sequence(['lang' => $brick->repeat(5, 8, $brick->alpha())]),
        ]);

        $script = $brick->repeat(4, 4, $brick->alpha());

        $region = $brick->either([
            $brick->repeat(2, 2, $brick->alpha()),
            $brick->repeat(3, 3, $brick->digit()),
        ]);

        $alphanum = $brick->either([$brick->alpha(), $brick->digit()]);

        $variant = $brick->either([
            $brick->repeat(5, 8, $alphanum),
            $brick->sequence([$brick->digit(), $brick->repeat(3, 3, $alphanum)])
        ]);

        $singleton = $brick->either([
            $brick->digit(),
            $brick->range(0x41, 0x57),
            $brick->range(0x59, 0x5A),
            $brick->range(0x61, 0x77),
            $brick->range(0x79, 0x7A),
        ]);

        $extension = $brick->sequence([
            $singleton,
            $brick->repeat(1, null, $brick->sequence(['-', $brick->repeat(2, 8, $alphanum)])),
        ]);

        $privateuse = $brick->sequence([
            'x',
            $brick->repeat(1, null, $brick->sequence(['-', $brick->repeat(1, 8, $alphanum)]))
        ]);

        $new = static fn (string $class) => new Custom(static fn (string $arg) => new $class($arg));

        $privateuse = $brick->transformation($new(PrivateUse::class), $privateuse);

        $irregular = $brick->either([
            'en-GB-oed',
            'i-ami',
            'i-bnn',
            'i-default',
            'i-enochian',
            'i-hak',
            'i-klingon',
            'i-lux',
            'i-mingo',
            'i-navajo',
            'i-pwn',
            'i-tao',
            'i-tay',
            'i-tsu',
            'sgn-BE-FR',
            'sgn-BE-NL',
            'sgn-CH-DE',
        ]);

        $regular = $brick->either([
            'art-lojban',
            'cel-gaulish',
            'no-bok',
            'no-nyn',
            'zh-guoyu',
            'zh-hakka',
            'zh-min',
            'zh-min-nan',
            'zh-xiang',
        ]);

        $grandfathered = $brick->either([
            $brick->transformation($new(Irregular::class), $irregular),
            $brick->transformation($new(Regular::class), $regular)
        ]);

        $langtag = $brick->transformation(
            new Custom(static fn (array $from): Standard => new Standard(
                $from['language']['lang'],
                $from['language']['extlang'] ?? null,
                $from['script'] ?? null,
                $from['region'] ?? null,
                $from['variant'] ?? null,
                $from['extension'] ?? null,
                $from['privateuse'] ?? null,
            )),
            $brick->sequence([
                'language' => $language,
                $brick->repeat(0, 1, $brick->sequence(['-', 'script' => $script])),
                $brick->repeat(0, 1, $brick->sequence(['-', 'region' => $region])),
                $brick->repeat(0, null, $brick->sequence(['-', 'variant' => $variant])),
                $brick->repeat(0, null, $brick->sequence(['-', 'extension' => $extension])),
                $brick->repeat(0, 1, $brick->sequence(['-', 'privateuse' => $privateuse])),
            ])
        );

        return $brick->either([$langtag, $privateuse, $grandfathered]);
    }
}
