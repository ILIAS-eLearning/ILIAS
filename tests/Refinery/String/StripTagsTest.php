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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class StripTagsTest extends TestCase
{
    private const STRING_TO_STRIP = "I <script>contain</a> tags.";
    private const EXPECTED_RESULT = "I contain tags.";
    
    private Refinery $f;
    private Transformation $strip_tags;

    protected function setUp() : void
    {
        $this->f = new Refinery(
            $this->createMock(DataFactory::class),
            $language = $this->createMock(ilLanguage::class)
        );
        $this->strip_tags = $this->f->string()->stripTags();
    }

    public function testTransform() : void
    {
        $res = $this->strip_tags->transform(self::STRING_TO_STRIP);
        $this->assertEquals(self::EXPECTED_RESULT, $res);
    }

    public function testNoString() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->strip_tags->transform(0);
    }
}
