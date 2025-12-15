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

namespace ILIAS\Questions\Presentation\Layout\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Language\Language;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface;

class EditOverview
{
    private bool $orderable = false;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly Editability $editability,
        private readonly URLBuilder $url_builder,
        private readonly Properties $answer_form_properties
    ) {
        $this->form = $this->buildForm();
    }

    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render($this->buildContent());
    }

    public function withRequest(
        ServerRequestInterface $request
    ): self {
        $clone = clone $this;
        $clone->form = $clone->form->withRequest($request);
        return $clone;
    }

    public function withOrderable(bool $orderable): self
    {
        $clone = clone $this;
        $clone->orderable = $orderable;
        return $clone;
    }

    private function buildContent(): array
    {
        return [
            $this->buildBasicAnswerFormPanel(),
            $this->answer_form_properties->getOverviewTable()
        ];
    }

    private function buildBasicAnswerFormPanel(): StandardPanel
    {
        $content = [
            $this->ui_factory->listing()->descriptive(
                $this->answer_form_properties->getBasicPropertiesForListing($this->lng)
            )
        ];

        if ($this->editability === Editability::Full) {
            $content[] = $this->ui_factory->button()->standard(
                $this->lng->txt('edit_basic_answer_form_properties'),
                $this->url_builder->buildURI()->__toString()
            );
        }

        return $this->ui_factory->panel()->standard(
            $this->lng->txt('basic_answer_form_properites'),
            $content
        );
    }
}
