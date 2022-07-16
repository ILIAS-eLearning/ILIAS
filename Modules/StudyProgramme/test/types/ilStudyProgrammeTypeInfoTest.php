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

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeTypeInfoTest extends TestCase
{
    private const VALID_TITLE_1 = 'Title 1';
    private const VALID_TITLE_2 = 'Title 2';
    private const VALID_TITLE_N = null;
    private const VALID_DESCRIPTION_1 = 'Description 1';
    private const VALID_DESCRIPTION_2 = 'Description 2';
    private const VALID_DESCRIPTION_N = null;
    private const VALID_LNG_CODE_1 = 'de';
    private const VALID_LNG_CODE_2 = 'en';
    private const VALID_LNG_CODE_N = null;

    public function testSuccessCreate() : void
    {
        $obj = new ilStudyProgrammeTypeInfo();

        $this->assertNull($obj->getTitle());
        $this->assertNull($obj->getDescription());
        $this->assertNull($obj->getLanguageCode());

        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_N,
            self::VALID_DESCRIPTION_N,
            self::VALID_LNG_CODE_N
        );

        $this->assertNull($obj->getTitle());
        $this->assertNull($obj->getDescription());
        $this->assertNull($obj->getLanguageCode());

        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_1,
            self::VALID_DESCRIPTION_1,
            self::VALID_LNG_CODE_1
        );

        $this->assertEquals(self::VALID_TITLE_1, $obj->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $obj->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $obj->getLanguageCode());
    }

    public function testSuccessfulWithTitle() : void
    {
        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_1,
            self::VALID_DESCRIPTION_1,
            self::VALID_LNG_CODE_1
        );

        $new = $obj->withTitle(self::VALID_TITLE_2);

        $this->assertEquals(self::VALID_TITLE_1, $obj->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $obj->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $obj->getLanguageCode());

        $this->assertEquals(self::VALID_TITLE_2, $new->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $new->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $new->getLanguageCode());
    }

    public function testSuccessfulWithDescription() : void
    {
        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_1,
            self::VALID_DESCRIPTION_1,
            self::VALID_LNG_CODE_1
        );

        $new = $obj->withDescription(self::VALID_DESCRIPTION_2);

        $this->assertEquals(self::VALID_TITLE_1, $obj->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $obj->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $obj->getLanguageCode());

        $this->assertEquals(self::VALID_TITLE_1, $new->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_2, $new->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $new->getLanguageCode());
    }

    public function testSuccessfulWithLanguageCode() : void
    {
        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_1,
            self::VALID_DESCRIPTION_1,
            self::VALID_LNG_CODE_1
        );

        $new = $obj->withLanguageCode(self::VALID_LNG_CODE_2);

        $this->assertEquals(self::VALID_TITLE_1, $obj->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $obj->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_1, $obj->getLanguageCode());

        $this->assertEquals(self::VALID_TITLE_1, $new->getTitle());
        $this->assertEquals(self::VALID_DESCRIPTION_1, $new->getDescription());
        $this->assertEquals(self::VALID_LNG_CODE_2, $new->getLanguageCode());
    }


    public function testToFormInput() : void
    {
        $lng = $this->createMock(ilLanguage::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);

        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $obj = new ilStudyProgrammeTypeInfo(
            self::VALID_TITLE_1,
            self::VALID_DESCRIPTION_1,
            self::VALID_LNG_CODE_1
        );

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(['title'], ['description'], ['meta_l_de'])
            ->will($this->onConsecutiveCalls('title', 'description', 'meta_l_de'))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery
        );

        /** @var ILIAS\UI\Implementation\Component\Input\Field\Text $text */
        $text = $field->getInputs()['title'];

        $this->assertInstanceOf(
            ILIAS\UI\Implementation\Component\Input\Field\Text::class,
            $text
        );

        /** @var ILIAS\UI\Implementation\Component\Input\Field\Textarea $textarea */
        $textarea = $field->getInputs()['description'];

        $this->assertInstanceOf(
            ILIAS\UI\Implementation\Component\Input\Field\Textarea::class,
            $textarea
        );
    }
}
