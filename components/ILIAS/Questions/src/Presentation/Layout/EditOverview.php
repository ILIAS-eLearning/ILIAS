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
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\URLBuilder;

class EditOverview implements Viewable
{
    public function __construct(
        private readonly Environment $environment,
        private readonly URLBuilder $target_to_edit_basic_answer_form_properties
    ) {
    }

    #[\Override]
    public function getUI(): array
    {
        return [
            $this->buildBasicAnswerFormPanel(),
            $this->environment->getAnswerFormProperties()->getOverviewTable(
                $this->environment
            )
        ];
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
