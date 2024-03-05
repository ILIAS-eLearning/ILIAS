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

namespace ILIAS\Refinery\String\Encoding;

use ILIAS\Tests\Refinery\TestCase;

class EncodingTest extends TestCase
{
    private ?Group $group = null;

    public function setUp(): void
    {
        $this->group = new Group();
    }

    public function latin1StringProvider(): array
    {
        // generate 500 random strings with ISO-8859-1 encoding. unfortunately, I was not able to find a list to copy
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
     * @dataProvider latin1StringProvider
     */
    public function testLatin1ToUTF8(
        string $latin_1_string,
        string $expected_utf8
    ): void {
        $this->assertTrue(mb_check_encoding($latin_1_string, 'ISO-8859-1'));
        $result = $this->group->latin1ToUtf8()->transform($latin_1_string);
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
        $this->assertEquals($expected_utf8, $result);
    }

    public function asciiStringProvider(): array
    {
        // generate 500 random strings with US-ASCII encosing.
        $strings = [];
        for ($i = 0; $i < 500; $i++) {
            $length = random_int(50, 500);
            $string = '';
            for ($j = 0; $j < $length; $j++) {
                $string .= chr(random_int(0, 127));
            }
            $strings[] = [$string, $string];
        }
        return $strings;
    }

    /**
     * @dataProvider asciiStringProvider
     */
    public function testAsciiToUTF8(
        string $latin_1_string,
        string $expected_utf8
    ): void {
        $this->assertTrue(mb_check_encoding($latin_1_string, 'US-ASCII'));
        $result = $this->group->asciiToUtf8()->transform($latin_1_string);
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
        $this->assertEquals($expected_utf8, $result);
    }

}
