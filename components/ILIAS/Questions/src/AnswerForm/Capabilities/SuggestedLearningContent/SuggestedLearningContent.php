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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\AnswerForm\Capabilities\Action;
use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\StaticURL\Services as StaticURLServices;

class SuggestedLearningContent implements CapabilityInterface
{
    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly \ilRbacSystem $rbac_system,
        private readonly \ilTree $tree,
        private readonly StaticURLServices $static_url,
        private readonly \ilObjUser $current_user,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return true;
    }

    #[\Override]
    public function getEditAction(): Action
    {
        return new Action(
            $this,
            'suggested_learning_content'
        );
    }

    #[\Override]
    public function edit(
        Environment $environment
    ): Async|Renderable {
        $step = $environment->getStep();
        $overview = $this->buildOverview($environment);
        if ($step === '') {
            return $overview;
        }

        return $overview->doAction(
            $step
        );
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
    }

    private function buildOverview(
        Environment $environment
    ): Overview {
        return new Overview(
            $this->ctrl,
            $this->rbac_system,
            $this->tree,
            $this->current_user,
            $this->static_url,
            $environment,
            $this->repository
        );
    }
}
