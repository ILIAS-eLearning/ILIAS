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

require_once './vendor/composer/vendor/autoload.php';
require_once './components/ILIAS/UI/tests/Base.php';

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\Container\ViewControl as VC;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;

class ViewControlContainerTest extends ILIAS_UI_TestBase
{
    use NameSourceStubs;

    protected function buildDataFactory(): Data\Factory
    {
        return new Data\Factory();
    }
    protected function buildRefinery(): Refinery
    {
        return new Refinery(
            $this->buildDataFactory(),
            $this->createMock(ILIAS\Language\Language::class)
        );
    }
    protected function buildContainerFactory(): VC\Factory
    {
        return new VC\Factory(
            new I\SignalGenerator(),
            $this->buildVCFactory(),
            $this->createCountingNameSourceStub('input_'),
        );
    }
    protected function buildFieldFactory(): FieldFactory
    {
        return new FieldFactory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\Field\Node\Factory::class),
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\HasDynamicInputsNameSource::class),
            $this->createMock(UploadLimitResolver::class),
            new I\SignalGenerator(),
            $this->buildDataFactory(),
            $this->buildRefinery(),
            $this->getLanguage()
        );
    }

    protected function buildVCFactory(): Control\Factory
    {
        return new Control\Factory(
            $this->buildFieldFactory(),
            $this->buildDataFactory(),
            $this->buildRefinery(),
            new I\SignalGenerator(),
            $this->getLanguage(),
        );
    }

    public function testViewControlContainerConstruct(): void
    {
        $vc = $this->buildContainerFactory()->standard([]);
        $this->assertInstanceOf(VC\ViewControl::class, $vc);
        $this->assertInstanceOf(I\Signal::class, $vc->getSubmissionSignal());
    }

    public function testViewControlContainerWithRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getQueryParams")
            ->willReturn([
                'input_1' => ['a1', 'a3'],
                'input_3' => 'a2',
                'input_4' => 'DESC'
            ]);

        $c_factory = $this->buildVCFactory();
        $controls = [
            $c_factory->fieldSelection(['a1' => 'A','a2' => 'B','a3' => 'C']),
            $c_factory->sortation([
                '2up' => new Data\Order('a2', 'ASC'),
                '2down' => new Data\Order('a2', 'DESC')
            ]),
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

    public function testViewControlContainerRenderWithoutRequest(): void
    {
        $this->expectException(\LogicException::class);

        $c_factory = $this->buildVCFactory();
        $controls = [
            $c_factory->fieldSelection(['a1' => 'A','a2' => 'B','a3' => 'C'])
        ];
        $vc = $this->buildContainerFactory()->standard($controls);
        $this->getDefaultRenderer()->render($vc);
    }

    public function testViewControlContainerTransforms(): void
    {
        $transform = $this->buildRefinery()->custom()->transformation(
            fn($v) => ['modified' => 'transformed']
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method("getQueryParams")
                ->willReturn(['some' => 'data']);

        $controls = [
            $this->buildVCFactory()->fieldSelection(['a1' => 'A'])
        ];
        $vc = $this->buildContainerFactory()->standard($controls)
            ->withAdditionalTransformation($transform)
            ->withRequest($request);


        $expected = ['modified' => 'transformed'];
        $this->assertEquals($expected, $vc->getData());
    }

    public function testExtractCurrentValues(): void
    {
        $c_factory = $this->buildVCFactory();
        $controls = [
            $c_factory->fieldSelection(['a1' => 'A','a2' => 'B','a3' => 'C'])
                ->withValue(['a1', 'a3']),
            $c_factory->sortation([
                '2up' => new Data\Order('a2', 'ASC'),
                '2down' => new Data\Order('a2', 'DESC')
            ])->withValue(['a2', 'DESC']),
        ];

        $vc = $this->buildContainerFactory()->standard($controls);
        $data = $vc->getComponentInternalValues();

        $this->assertEquals(
            [
                'input_1' => ['a1', 'a3'],
                'input_3' => 'a2',
                'input_4' => 'DESC'
            ],
            $data
        );
    }
}
