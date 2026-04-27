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

namespace ILIAS\Questions;

use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\AnswerForm\Capabilities\Edit as CapabilitiesEditView;
use ILIAS\Questions\AnswerForm\Capabilities\Factory as CapabilitiesFactory;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Presentation\Layout\Factory as LayoutFactory;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Presentation\Views\Participant;
use ILIAS\Questions\PublicInterface;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Style\Content\Service as ContentStyle;
use ILIAS\GlobalScreen\Services as GlobalScreen;

class DefaultPublicInterface implements PublicInterface
{
    public function __construct(
        private readonly Language $lng,
        private readonly \ilObjUser $current_user,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalScreen $global_screen,
        private readonly GlobalTemplate $global_tpl,
        private readonly ContentStyle $content_style,
        private readonly \ilCtrl $ctrl,
        private readonly HTTP $http,
        private readonly \ilTabsGUI $tabs_gui,
        private readonly \ilUIService $ui_services,
        private readonly UuidFactory $uuid_factory,
        private readonly ConfigurationRepository $configuration_repository,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly AttemptRepository $attempt_repository,
        private readonly LayoutFactory $layout_factory,
        private readonly CapabilitiesFactory $capabilities_factory,
        private CapabilitiesEditView $capabilities_edit_view
    ) {
    }

    #[\Override]
    public function getParticipantView(
        int $owner_obj_id
    ): Participant {
        return new Participant(
            $this->ui_factory,
            $this->capabilities_factory,
            $this->questions_repository,
            $this->attempt_repository,
            $owner_obj_id
        );
    }

    #[\Override]
    public function getEditView(
        int $owner_obj_id
    ): Edit {
        return new Edit(
            $this->lng,
            $this->current_user,
            $this->refinery,
            $this->ui_factory,
            $this->ui_renderer,
            $this->global_screen,
            $this->global_tpl,
            $this->content_style,
            $this->ctrl,
            $this->http,
            $this->tabs_gui,
            $this->ui_services,
            $this->uuid_factory,
            $this->configuration_repository,
            $this->answer_form_factory,
            $this->questions_repository,
            $this->layout_factory,
            $this->capabilities_edit_view,
            $owner_obj_id
        );
    }
}
