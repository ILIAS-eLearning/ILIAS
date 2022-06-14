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

namespace ILIAS\Data\RFC;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Closure;

/**
 * RFC 5646 compliant language tag definition (https://www.ietf.org/rfc/bcp/bcp47.txt).
 */
class LanguageTagDefinition
{
    private Brick $brick;

    public function __construct(Brick $brick)
    {
        $this->brick = $brick;
    }

    /**
     * @param Brick $brick
     * @param string $input
     * @return Result<string>
     */
    public function parse(string $input) : Result
    {
        return $this->brick->apply($this->definition($this->brick), new Intermediate($input))->map(static function (Intermediate $x) : string {
            return $x->accepted();
        })->except(static function () : Result {
            return new Error('Given string is no valid language tag.');
        });
    }

    /**
     * This definition is directly translated from the ABNF definition on https://www.ietf.org/rfc/bcp/bcp47.txt.
     */
    public function definition(Brick $brick) : Closure
    {
        $extlang = $brick->sequence([
            $brick->repeat(3, 3, $brick->alpha()),
            $brick->repeat(0, 2, $brick->sequence(["-", $brick->repeat(3, 3, $brick->alpha())])),
        ]);

        $language = $brick->either([
            $brick->sequence([
                $brick->repeat(2, 3, $brick->alpha()),
                $brick->repeat(0, 1, $brick->sequence(['-', $extlang])),
            ]),
            $brick->repeat(4, 4, $brick->alpha()),
            $brick->repeat(5, 8, $brick->alpha()),
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

        $grandfathered = $brick->either([$irregular, $regular]);

        $langtag = $brick->sequence([
            $language,
            $brick->repeat(0, 1, $brick->sequence(['-', $script])),
            $brick->repeat(0, 1, $brick->sequence(['-', $region])),
            $brick->repeat(0, null, $brick->sequence(['-', $variant])),
            $brick->repeat(0, null, $brick->sequence(['-', $extension])),
            $brick->repeat(0, 1, $brick->sequence(['-', $privateuse])),
        ]);

        return $brick->either([
            $langtag,
            $privateuse,
            $grandfathered,
        ]);
    }
}
