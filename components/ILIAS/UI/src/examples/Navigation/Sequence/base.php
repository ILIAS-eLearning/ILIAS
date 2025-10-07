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

namespace ILIAS\UI\examples\Navigation\Sequence;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Navigation\Sequence\SegmentRetrieval;
use ILIAS\UI\Component\Navigation\Sequence\SegmentBuilder;
use ILIAS\UI\Component\Navigation\Sequence\Segment;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ---
 * description: >
 *   Base example for rendering a sequence navigation.
 *
 * expected output: >
 *   ILIAS shows a group of buttons and different placeholders for the segment.
 *   The navigation buttons are "back" and "next". At first, the "back" button is
 *   inactive until the next button was clicked. On the last segment, the "next"
 *   button will be inactive.
 *   A view control allows the user to select chunks of data, and an additional
 *   button (without real function) is labeled "a global action".
 *   On some segments there is an additional button labeled "a segment action
 *   for pos x". In this example, these buttons don't trigger a function.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();

    $binding = new class ($f, $r) implements SegmentRetrieval {
        private array $seq_data;

        public function __construct(
            protected UIFactory $f,
            protected UIRenderer $r
        ) {
            $this->seq_data = [
                ['c0', 'pos 1', '<div style="width: 100%;
                                            height: 500px;
                                            background-color: #b8d7ea;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;">
                                            placeholder for the segment at position 1</div>'],
                ['c0', 'pos 2', '<div style="width: 100%;
                                            height: 700px;
                                            background-color: #f6d9a1;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;">
                                            placeholder for the segment at position 2</div>'],
                ['c1', 'pos 3', 'the segment at position 3'],
                ['c2', 'pos 4', 'the segment at position 4'],
                ['c1', 'pos 5', 'the segment at position 5'],
            ];
        }

        public function getAllPositions(
            ServerRequestInterface $request,
            mixed $viewcontrol_values,
            mixed $filter_values,
        ): array {
            $chunks = $viewcontrol_values['chunks'] ?? [];
            $chunks[] = 'c0';
            return array_values(
                array_filter(
                    $this->seq_data,
                    fn($posdata) => in_array($posdata[0], $chunks)
                )
            );
        }

        public function getSegment(
            ServerRequestInterface $request,
            mixed $position_data,
            mixed $viewcontrol_values,
            mixed $filter_values,
        ): Segment {
            list($chunk, $title, $data) = $position_data;

            $segment = $this->f->legacy()->segment($title, $data);

            if ($chunk === 'c0') {
                $segment = $segment->withSegmentActions(
                    $this->f->button()->standard('a segment action for ' . $title, '#')
                );
            }
            return $segment;
        }
    };

    $viewcontrols = $f->input()->container()->viewControl()->standard([
        $f->input()->viewControl()->fieldSelection(
            [
                'c1' => 'chunk 1',
                'c2' => 'chunk 2',
            ],
            'shown chunks',
            'apply'
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(
            fn($v) => ['chunks' => $v]
        ))
    ])
    ->withAdditionalTransformation(
        $refinery->custom()->transformation(fn($v) => array_shift($v))
    );

    $global_actions = [
        $f->button()->standard('a global action', '#')
    ];

    $sequence = $f->navigation()->sequence($binding)
        ->withViewControls($viewcontrols)
        ->withId('example')
        ->withActions($global_actions)
        ->withRequest($request);

    $out = [];
    $out[] = $sequence;

    return $r->render($out);
}
