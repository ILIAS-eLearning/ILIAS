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

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\UI\Implementation\Component\Input\Container\ViewControl as VC;
use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;

class ViewControlContainerTest extends ILIAS_UI_TestBase
{
    protected function buildDataFactory(): Data\Factory
    {
        return new Data\Factory();
    }
    protected function buildRefinery(): Refinery
    {
        return new Refinery(
            $this->buildDataFactory(),
            $this->createMock(ilLanguage::class)
        );
    }
    protected function buildContainerFactory(): VC\Factory
    {
        return new VC\Factory(
            new I\SignalGenerator()
        );
    }
    protected function buildVCFactory(): Control\Factory
    {
        return new Control\Factory(
            $this->buildDataFactory(),
            $this->buildRefinery(),
            new I\SignalGenerator()
        );
    }

    public function testViewControlContainerConstruct(): void
    {
        $vc = $this->buildContainerFactory()->standard([]);
        $this->assertInstanceOf(VC\ViewControl::class, $vc);
        $this->assertInstanceOf(I\Signal::class, $vc->getSubmissionSignal());
    }

    public function testViewControlContainerWithControls(): void
    {
        $c_factory = $this->buildVCFactory();
        $controls = [
            $c_factory->fieldSelection([]),
            $c_factory->sortation([]),
            $c_factory->pagination()
        ];

        $vc = $this->buildContainerFactory()->standard($controls);
        $this->assertSameSize($controls, $vc->getInputs());

        $name_source = new FormInputNameSource();
        $named = array_map(
            fn ($input) => $input->withNameFrom($name_source),
            $controls
        );
        $this->assertEquals($named, $vc->getInputs());
    }

    public function testViewControlContainerWithRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getQueryParams")
            ->willReturn([
                'form_input_0' => 'a1,a3',
                'form_input_1' => 'a2:DESC'
            ]);

        $c_factory = $this->buildVCFactory();
        $controls = [
            $c_factory->fieldSelection(['a1'=>'A','a2'=>'B','a3'=>'C']),
            $c_factory->sortation(['a2:ASC'=>'2up','a2:DESC'=>'2down']),
        ];

        $vc = $this->buildContainerFactory()->standard($controls);
        $vc2 = $vc->withRequest($request);
        $this->assertNotSame($vc2, $vc);

        $data = $vc2->getData();
        $this->assertSameSize($controls, $data);

        $expected = [
            ['a1','a3'],
            $this->buildDataFactory()->order('a2', 'DESC')
        ];
        $this->assertEquals($expected, array_values($data));
    }


    public function testViewControlContainerTransforms(): void
    {
        $vc = $this->buildContainerFactory()->standard([]);
        $transform = $this->buildRefinery()->custom()->transformation(
            fn ($v) => ['modified' => $v]
        );
        $vc = $this->buildContainerFactory()->standard([])
            ->withAdditionalTransformation($transform);

        $expected = ['modified' => []];
        $this->assertEquals($expected, $vc->getData());
    }
}
