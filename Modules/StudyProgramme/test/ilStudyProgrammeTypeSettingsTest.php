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

class ilStudyProgrammeTypeSettingsTest extends TestCase
{
    private const VALID_TYPE_1 = 11;
    private const VALID_TYPE_2 = 22;

    public function testSuccessCreate() : void
    {
        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $this->assertEquals(self::VALID_TYPE_1, $obj->getTypeId());
    }

    public function testSuccessfulWithTypeId() : void
    {
        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $new = $obj->withTypeId(self::VALID_TYPE_2);

        $this->assertEquals(self::VALID_TYPE_1, $obj->getTypeId());
        $this->assertEquals(self::VALID_TYPE_2, $new->getTypeId());
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

        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(['type'], ['prg_type_byline'], ['prg_type'])
            ->will($this->onConsecutiveCalls('type', 'prg_type_byline', 'prg_type'))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery,
            [
                self::VALID_TYPE_1 => 'first',
                self::VALID_TYPE_2 => 'second'
            ]
        );

        /** @var ILIAS\UI\Implementation\Component\Input\Field\Select $select */
        $select = $field->getInputs()['type'];

        $this->assertInstanceOf(
            ILIAS\UI\Implementation\Component\Input\Field\Select::class,
            $select
        );
        $this->assertEquals(self::VALID_TYPE_1, $select->getValue());
    }
}
