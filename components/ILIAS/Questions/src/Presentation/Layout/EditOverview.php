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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

class EditOverview implements Renderable
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly ServerRequestInterface $request,
        private readonly Environment $environment,
        private readonly URLBuilder $target_to_edit_basic_answer_form_properties
    ) {
    }

    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render($this->buildContent());
    }

    private function buildContent(): array
    {
        return [
            $this->buildBasicAnswerFormPanel(),
            $this->environment->getAnswerFormProperties()->getOverviewTable(
                $this->ui_factory->table(),
                $this->lng,
                $this->request,
                $this->environment
            )
        ];
    }

    private function buildBasicAnswerFormPanel(): StandardPanel
    {
        $content = [
            $this->ui_factory->listing()->descriptive(
                $this->environment->getAnswerFormProperties()->getBasicPropertiesForListing($this->lng)
            )
        ];

        if ($this->environment->getEditability() === Editability::Full) {
            $content[] = $this->ui_factory->button()->standard(
                $this->lng->txt('edit_basic_answer_form_properties'),
                $this->target_to_edit_basic_answer_form_properties
                    ->buildURI()
                    ->__toString()
            );
        }

        return $this->ui_factory->panel()->standard(
            $this->lng->txt('basic_answer_form_properites'),
            $content
        );
    }
}
