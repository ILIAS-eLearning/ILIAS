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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Signal;

require_once('ViewControlBaseTest.php');

class ViewControlGenericTest extends ViewControlBaseTest
{
    protected function getViewControl(): Control\ViewControlInput
    {
        return new class (
            $this->buildDataFactory(),
            $this->buildRefinery(),
            ''
        ) extends Control\ViewControlInput {
            public function isClientSideValueOk($value): bool
            {
                return true;
            }
            protected function getDefaultValue(): string
            {
                return 'default';
            }
        };
    }

    public function testViewControlSortationMutators(): void
    {
        $vc = $this->getViewControl();
        $v = 'some value';
        $l = 'some label';
        $s = (new SignalGenerator())->create();
        $this->assertEquals($v, $vc->withValue($v)->getValue());
        $this->assertEquals($s, $vc->withOnChange($s)->getOnChangeSignal());
    }

    public function testViewControlWithInput(): void
    {
        $v = 'some input value';

        $input = $this->createMock(InputData::class);
        $input->expects($this->exactly(2))
            ->method("getOr")
            ->willReturn($v);

        $vc = $this->getViewControl()
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok($v),
            $vc->getContent()
        );
        $this->assertEquals($v, $vc->getValue());

        $transform = $this->buildRefinery()->custom()->transformation(
            fn ($v) => ['mod' => $v]
        );
        $vc = $vc->withAdditionalTransformation($transform);
        $this->assertEquals(
            ['mod' => $v],
            $vc->withInput($input)->getContent()->value()
        );
    }
}
