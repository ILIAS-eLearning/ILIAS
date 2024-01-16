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

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Implementation\Component;

class mock_ilOrgUnitTypeGUI extends ilOrgUnitTypeGUI
{
    public function __construct(
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\UI\Factory $ui_factory,
        protected ilLanguage $lng,
    ) {
        $this->lng = $lng;
    }

    public function mockGetAmdForm(array $available_records, ilOrgUnitType $type): StandardForm
    {
        return $this->getAmdForm('#', $available_records, $type);
    }
}

class ilModulesOrgUnitTypeTest extends TestCase
{
    public function getRefinery(): \ILIAS\Refinery\Factory
    {
        $data_factory = new \ILIAS\Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        $refinery = new \ILIAS\Refinery\Factory($data_factory, $language);
        return $refinery;
    }

    public function getUIFactory(): NoUIFactory
    {
        $language = $this->createMock(ilLanguage::class);
        $filter_factory = $this->createMock(Component\Input\Container\Filter\Factory::class);
        $view_control_factory = $this->createMock(Component\Input\Container\ViewControl\Factory::class);
        $control_factory = $this->createMock(Component\Input\ViewControl\Factory::class);
        $upload_limit_resolver = $this->createMock(Component\Input\UploadLimitResolver::class);
        $refinery = $this->getRefinery();

        $factory = new class (
            $language,
            $filter_factory,
            $view_control_factory,
            $control_factory,
            $upload_limit_resolver,
            $refinery
        ) extends NoUIFactory {
            public function __construct(
                protected $language,
                protected $filter_factory,
                protected $view_control_factory,
                protected $control_factory,
                protected $upload_limit_resolver,
                protected $refinery
            ) {
            }

            public function input(): ILIAS\UI\Component\Input\Factory
            {
                $signal_generator = new Component\SignalGenerator();
                $data_factory = new \ILIAS\Data\Factory();

                $field_factory = new Component\Input\Field\Factory(
                    $this->upload_limit_resolver,
                    $signal_generator,
                    $data_factory,
                    $this->refinery,
                    $this->language
                );

                $form_factory = new Component\Input\Container\Form\Factory(
                    $field_factory
                );
                $container_factory = new Component\Input\Container\Factory(
                    $form_factory,
                    $this->filter_factory,
                    $this->view_control_factory
                );

                return new Component\Input\Factory(
                    $signal_generator,
                    $field_factory,
                    $container_factory,
                    $this->control_factory,
                );
            }
        };
        return $factory;
    }

    public function testOrgUnitTypeGuiAMDForm(): void
    {
        $amdr1 = $this->createMock(ilAdvancedMDRecord::class);
        $amdr1
            ->method('getRecordId')
            ->willReturn(1);
        $amdr1
            ->method('getTitle')
            ->willReturn('title 1');

        $amdr2 = $this->createMock(ilAdvancedMDRecord::class);
        $amdr2
            ->method('getRecordId')
            ->willReturn(2);
        $amdr2
            ->method('getTitle')
            ->willReturn('title 2');

        $type = $this->createMock(ilOrgUnitType::class);
        $type
            ->method('getAssignedAdvancedMDRecordIds')
            ->willReturn([1]);

        $gui = new mock_ilOrgUnitTypeGUI(
            $this->getRefinery(),
            $this->getUIFactory(),
            $this->createMock(ilLanguage::class)
        );

        $form = $gui->mockGetAmdForm([$amdr1, $amdr2], $type);
        $this->assertInstanceOf(StandardForm::class, $form);

        $section = current($form->getInputs());
        $this->assertInstanceOf(Section::class, $section);

        $inputs = $section->getInputs();
        $this->assertEquals(1, count($inputs));
        $this->assertEquals(
            [
                1 => 'title 1',
                2 => 'title 2',
            ],
            current($inputs)->getOptions()
        );
    }
}
