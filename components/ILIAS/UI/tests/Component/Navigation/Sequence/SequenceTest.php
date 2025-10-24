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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Navigation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

class SequenceTest extends ILIAS_UI_TestBase
{
    protected I\Button\Factory $button_factory;
    protected I\Button\Standard $button_stub;
    protected string $button_html;
    protected I\Symbol\Factory $symbol_factory;
    protected Refinery $refinery;
    protected DataFactory $data_factory;
    protected ServerRequestInterface $request;

    public function setUp(): void
    {
        $this->button_stub = $this->createMock(I\Button\Standard::class);
        $this->button_html = sha1(I\Button\Standard::class);
        $this->button_stub->method('getCanonicalName')->willReturn($this->button_html);
        $this->button_stub->method('withSymbol')->willReturn($this->button_stub);
        $this->button_stub->method('withUnavailableAction')->willReturn($this->button_stub);

        $this->button_factory = $this->createMock(I\Button\Factory::class);
        $this->button_factory->method('standard')->willReturn($this->button_stub);

        $this->symbol_factory = $this->createMock(I\Symbol\Factory::class);

        $lang = $this->getLanguage();
        $this->data_factory = new DataFactory();
        $this->refinery = new Refinery($this->data_factory, $lang);

        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->request->method("getUri")
            ->willReturn(new \GuzzleHttp\Psr7\Uri('http://localhost:80/doil'));

        parent::setUp();
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class (
            $this->button_factory,
            $this->symbol_factory,
        ) extends NoUIFactory {
            public function __construct(
                protected I\Button\Factory $button_factory,
                protected I\Symbol\Factory $symbol_factory,
            ) {
            }

            public function button(): I\Button\Factory
            {
                return $this->button_factory;
            }
            public function symbol(): I\Symbol\Factory
            {
                return $this->symbol_factory;
            }

        };
    }

    protected function getNavigationFactory(): Navigation\Factory
    {
        return new Navigation\Factory(
            $this->data_factory,
            $this->refinery,
            $this->createMock(ILIAS\UI\Storage::class)
        );
    }

    public function testSequenceNavigationBasics(): void
    {
        $sequence = $this->getNavigationFactory()->sequence(
            $this->createMock(C\Navigation\Sequence\SegmentRetrieval::class)
        );

        $id = 'some_id';
        $view_controls = $this->createMock(I\Input\Container\ViewControl\ViewControl::class);
        $actions = [
            $this->createMock(I\Button\Standard::class),
            $this->createMock(I\Button\Standard::class),
        ];

        $this->assertEquals($view_controls, $sequence->withViewControls($view_controls)->getViewControls());
        $this->assertEquals($actions, $sequence->withActions(...$actions)->getActions());
        $this->assertEquals($this->request, $sequence->withRequest($this->request)->getRequest());
        $this->assertStringContainsString(
            'sequence_' . $id . '_p=1',
            $sequence->withId($id)->withRequest($this->request)->getNext(1)->__toString()
        );
    }

    public function testSequenceNavigationRendering(): void
    {
        $binding = new class () implements C\Navigation\Sequence\SegmentRetrieval {
            public function getAllPositions(
                ServerRequestInterface $request,
                mixed $viewcontrol_values,
                mixed $filter_values,
            ): array {
                return [
                    ['pos 1', 'content 1'],
                    ['pos 2', 'content 2'],
                ];
            }
            public function getSegment(
                ServerRequestInterface $request,
                mixed $position_data,
                mixed $viewcontrol_values,
                mixed $filter_values,
            ): I\Legacy\Segment {
                return new I\Legacy\Segment('title', 'content');
            }
        };

        $sequence = $this->getNavigationFactory()->sequence($binding)->withRequest($this->request);
        $actual_html = $this->getDefaultRenderer(null, [$this->button_stub])
            ->render($sequence);

        $expected_html = <<<EOT
            <div class="c-sequence c-sequence--linear">
                <div class="c-sequence__header">

                    <div  id="id_3" class="c-sequence__navigation">
                        <div class="c-sequence__navigation--back">
                            {$this->button_html}
                        </div>
                        <div class="c-sequence__navigation--next">
                            {$this->button_html}
                        </div>
                    </div>

                    <div class="c-sequence__header__segment">
                        <div class="c-sequence__header__segment__title">
                            <div id="id_2">title</div>
                        </div>
                    </div>

                </div>

                <div class="c-sequence__segment">
                    <div id="id_1" class="c-sequence__segment__contents">
                        content
                    </div>
                </div>

            </div>
        EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html)
        );
    }

    public function testSequenceNavigationPositionsFromQuery(): void
    {
        $request = $this->request;
        $request
            ->method('getQueryParams')
            ->willReturn(['sequence__p' => 12]);
        $sequence = $this->getNavigationFactory()->sequence(
            $this->createMock(C\Navigation\Sequence\SegmentRetrieval::class)
        )->withRequest($request);

        $this->assertEquals(12, $sequence->getCurrentPosition());
        $this->assertStringContainsString('_p=11', $sequence->getNext(-1)->__toString());
    }


    public function testSequenceNavigationTitleRendering(): void
    {
        $binding = new class () implements C\Navigation\Sequence\SegmentRetrieval {
            public function getAllPositions(
                ServerRequestInterface $request,
                mixed $viewcontrol_values,
                mixed $filter_values,
            ): array {
                return [null];
            }
            public function getSegment(
                ServerRequestInterface $request,
                mixed $position_data,
                mixed $viewcontrol_values,
                mixed $filter_values,
            ): I\Legacy\Segment {
                return new I\Legacy\Segment('title', 'content');
            }
        };

        $title = 'Sequence Title';
        $sequence = $this->getNavigationFactory()->sequence($binding, $title)
            ->withRequest($this->request);
        $actual_html = $this->getDefaultRenderer(null, [$this->button_stub])
            ->render($sequence);

        $expected_html = <<<EOT
            <div class="c-sequence c-sequence--linear">
                <h2 class="ilHeader">{$title}</h2>
                <div class="c-sequence__header">

                    <div  id="id_3" class="c-sequence__navigation">
                        <div class="c-sequence__navigation--back">
                            {$this->button_html}
                        </div>
                        <div class="c-sequence__navigation--next">
                            {$this->button_html}
                        </div>
                    </div>

                    <div class="c-sequence__header__segment">
                        <div class="c-sequence__header__segment__title">
                            <div id="id_2">title</div>
                        </div>
                    </div>

                </div>

                <div class="c-sequence__segment">
                    <div id="id_1" class="c-sequence__segment__contents">
                        content
                    </div>
                </div>

            </div>
        EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html)
        );
    }
}
