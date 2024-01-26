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

namespace ILIAS\Filesystem\Util;

use PHPUnit\Framework\TestCase;
use ILIAS\Filesystem\Util;

require_once("./include/Unicode/UtfNormal.php");

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FilenameSanitizing extends TestCase
{
    public function provideFilenames() : array
    {
        return [
            ["Control\u{00a0}Character", 'ControlCharacter'],
            ["Soft\u{00ad}Hyphen", 'SoftHyphen'],
            ["No\u{0083}Break", 'NoBreak'],
            ["ZeroWidth\u{200C}NonJoiner", 'ZeroWidthNonJoiner'],
            ["ZeroWidth\u{200d}Joiner", 'ZeroWidthJoiner'],
            ["Invisible\u{2062}Times", 'InvisibleTimes'],
            ["Invisible\u{2063}Comma", 'InvisibleComma'],
            ["Funky\u{200B}Whitespace", 'FunkyWhitespace'],
        ];
    }

    /**
     * @dataProvider provideFilenames
     */
    public function testSanitize(string $filename, string $expected) : void
    {
        $this->assertEquals($expected, Util::sanitizeFilename($filename));
    }
}
