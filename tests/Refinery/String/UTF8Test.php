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
use ILIAS\Refinery\String\Transformation\UTFNormalTransformation;

class UTF8Test extends TestCase
{
    private ?\ILIAS\Refinery\String\UTF8 $transformation = null;

    public function setUp(): void
    {
        $language = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $group = new Group(new Factory(), $language);

        $this->transformation = $group->utf8();
    }


    public function stringProvider(): array
    {
        // generate 50 random strings with ISO-8859-1 encoding. unfortunately, I was not able to find a list to copy
        // here which keeps it's encoding, therefore we must generate them randomly
        $strings = [];
        for ($i = 0; $i < 500; $i++) {
            $length = random_int(50, 500);
            $string = '';
            for ($j = 0; $j < $length; $j++) {
                $string .= chr(random_int(0, 255));
            }
            $strings[] = [$string, @utf8_encode($string)]; //  we must suppress the deprecation warning here
        }
        return $strings;
    }

    /**
     * @dataProvider stringProvider
     */
    public function testNormalization(
        string $string,
        string $expected_utf8
    ): void {
        $this->assertTrue(mb_check_encoding($string, 'ISO-8859-1'));
        $result = $this->transformation->transform($string);
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
        $this->assertEquals($expected_utf8, $result);
    }

}
