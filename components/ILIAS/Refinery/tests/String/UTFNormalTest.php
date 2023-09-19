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

namespace ILIAS\src\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\Group;
use ILIAS\Tests\Refinery\TestCase;
use ilLanguage;
use InvalidArgumentException;
use ILIAS\Refinery\String\Transformation\UTFNormalTransformation;
use ILIAS\Refinery\Transformation;

class UTFNormalTest extends TestCase
{
    private Transformation $form_d;
    private Transformation $form_c;
    private Transformation $form_kc;
    private Transformation $form_kd;

    public function setUp(): void
    {
        $language = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $group = new Group(new Factory(), $language);

        $this->form_d = $group->utfnormal()->formD();
        $this->form_c = $group->utfnormal()->formC();
        $this->form_kc = $group->utfnormal()->formKC();
        $this->form_kd = $group->utfnormal()->formKD();
    }

    public function stringProvider(): array
    {
        // Never ever try to change something on this array :-) e.g. a 'ä' isn't a 'ä' but a 'ä' ;-)
        return [
            ["Ä\uFB03n", "Ä\uFB03n", 'Ä\uFB03n', 'Ä\uFB03n', 'Ä\uFB03n'],
            ["\xC3\x85", 'Å', 'Å', 'Å', 'Å'],
            ["\xCC\x8A", '̊', '̊', '̊', '̊'],
            ["\u{FFDA}", 'ￚ', 'ￚ', 'ᅳ', 'ᅳ'],
            ["\u{FDFA}", 'ﷺ', 'ﷺ', 'صلى الله عليه وسلم', 'صلى الله عليه وسلم'],
            ["\xF5", '', '', '', ''],
            ["ä", 'ä', 'ä', 'ä', 'ä'],
            ["🤔", "🤔", "🤔", "🤔", "🤔"],
            ["你好", "你好", "你好", "你好", "你好"],
        ];
    }

    /**
     * @dataProvider stringProvider
     */
    public function testNormalization(
        string $string,
        string $expected_form_c,
        string $expected_form_d,
        string $expected_form_kc,
        string $expected_form_kd
    ): void {
        // FORM C
        $this->assertEquals($expected_form_c, $this->form_c->transform($string));

        // FORM D
        $this->assertEquals($expected_form_d, $this->form_d->transform($string));

        // FORM KC
        $this->assertEquals($expected_form_kc, $this->form_kc->transform($string));

        // FORM KD
        $this->assertEquals($expected_form_kd, $this->form_kd->transform($string));
    }

    public function testUmlaut(): void
    {
        $char_A_ring = "\xC3\x85"; // 'LATIN CAPITAL LETTER A WITH RING ABOVE' (U+00C5)
        $char_combining_ring_above = 'A' . "\xCC\x8A";  // 'COMBINING RING ABOVE' (U+030A)

        $this->assertNotEquals($char_A_ring, $char_combining_ring_above);
        $this->assertNotEquals(bin2hex($char_A_ring), bin2hex($char_combining_ring_above));
        $tranformation = $this->form_d;
        $this->assertEquals('Å', $tranformation->transform($char_A_ring));
        $this->assertEquals(bin2hex('Å'), bin2hex($tranformation->transform($char_A_ring)));
        $this->assertEquals('Å', $tranformation->transform($char_combining_ring_above));
        $this->assertEquals(bin2hex('Å'), bin2hex($tranformation->transform($char_combining_ring_above)));
        $this->assertEquals(
            $tranformation->transform($char_A_ring),
            $this->form_kd->transform($char_combining_ring_above)
        );
        $this->assertEquals(
            bin2hex($tranformation->transform($char_A_ring)),
            bin2hex($this->form_kd->transform($char_combining_ring_above))
        );
    }
}
