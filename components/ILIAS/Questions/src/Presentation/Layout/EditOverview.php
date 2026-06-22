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
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\Ordering as OrderingTable;
use ILIAS\UI\Component\ViewControl\Mode as ViewControlMode;
use ILIAS\UI\URLBuilder;

class EditOverview implements Viewable
{
    private ?ViewControlMode $view_control = null;
    /**
     * @var list<StandardButton> $buttons
     */
    private array $buttons = [];
    private DataTable|OrderingTable|null $table = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly URLBuilder $target_to_edit_basic_answer_form_properties
    ) {
    }

    #[\Override]
    public function getUI(): array
    {
        $ui = [
            $this->buildBasicAnswerFormPanel(),

        ];

        if ($this->view_control !== null) {
            $ui[] = $this->view_control;
        }

        $ui_with_buttons = [
            ...$ui,
            ...$this->buttons
        ];

        if ($this->table !== null) {
            $ui_with_buttons[] = $this->table;
        }

        return $ui_with_buttons;
    }

    public function withViewControl(
        ViewControlMode $view_control
    ): self {
        $clone = clone $this;
        $clone->view_control = $view_control;
        return $clone;
    }

    public function withAdditionalButton(
        StandardButton $button
    ): self {
        $clone = clone $this;
        $clone->buttons[] = $button;
        return $clone;
    }

    public function withTable(
        DataTable|OrderingTable $table
    ): self {
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    private function buildBasicAnswerFormPanel(): StandardPanel
    {
        $content = [
            $this->environment->getUIFactory()->listing()->descriptive(
                $this->environment->getAnswerFormProperties()
                    ->getBasicPropertiesForListing(
                        $this->environment
                    )
            )
        ];

        if ($this->environment->getEditability() === Editability::Full) {
            $content[] = $this->environment->getUIFactory()->button()->standard(
                $this->environment->getLanguage()->txt('edit_basic_answer_form_properties'),
                $this->target_to_edit_basic_answer_form_properties
                    ->buildURI()
                    ->__toString()
            );
        }

        return $this->environment->getUIFactory()->panel()->standard(
            $this->environment->getLanguage()->txt('basic_answer_form_properties'),
            $content
        );
    }
}
