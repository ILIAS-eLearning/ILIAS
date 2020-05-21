<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeTypeInfoTest extends TestCase
{
    const VALID_TITLE_1 = 'Title 1';
    const VALID_TITLE_2 = 'Title 2';
    const VALID_TITLE_N = null;
    const VALID_DESCRIPTION_1 = 'Description 1';
    const VALID_DESCRIPTION_2 = 'Description 2';
    const VALID_DESCRIPTION_N = null;
    const VALID_LNG_CODE_1 = 'de';
    const VALID_LNG_CODE_2 = 'en';
    const VALID_LNG_CODE_N = null;


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
