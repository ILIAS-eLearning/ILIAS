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

namespace ILIAS\MetaData\OERHarvester\ControlCenter;

use ilPermissionException;
use ilGlobalTemplateInterface;
use ilCtrlInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcherInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;
use ILIAS\MetaData\OERHarvester\ControlCenter\Content\ContentFactoryInterface;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\RequestParserInterface;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as PublishingSettings;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\Handler as ObjectHandler;

class ControlCenterGUI
{
    protected int $ref_id;
    protected int $obj_id;
    protected string $type;
    protected StateInfoInterface $state_info;

    public function __construct(
        protected URI $link_to_parent,
        protected ilCtrlInterface $ctrl,
        protected ilGlobalTemplateInterface $tpl,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected RequestParserInterface $request_parser,
        protected ContentFactoryInterface $content_factory,
        protected PresentationUtilities $presentation_utilities,
        protected StateInfoFetcherInterface $state_info_fetcher,
        protected PublisherInterface $state_changer,
        protected PublishingSettings $publishing_settings,
        protected StaticURL $static_url,
        protected DataFactory $data_factory,
        protected ObjectHandler $object_handler
    ) {
        $this->ref_id = $this->request_parser->fetchRefID();
        $this->obj_id = $this->request_parser->fetchObjID();
        $this->type = $this->request_parser->fetchType();
        $this->state_info = $this->state_info_fetcher->getStateInfoForObjectReference(
            $this->ref_id,
            $this->obj_id,
            $this->type
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = Command::tryFrom($this->ctrl->getCmd());
        switch ($next_class) {
            default:
                if (!$cmd || !$this->isCommandAvailable($cmd)) {
                    throw new ilPermissionException($this->presentation_utilities->txt('permission_denied'));
                }
                $cmd_value = $cmd->value;
                $this->$cmd_value();
                break;
        }
    }

    /**
     * Includes access checks via StateInfo
     */
    protected function isCommandAvailable(Command $cmd): bool
    {
        if (!$this->state_info->isPublishingRelevant()) {
            return false;
        }

        return match ($cmd) {
            Command::VIEW => true,
            Command::BLOCK => $this->state_info->isActionAvailable(Action::BLOCK),
            Command::UNBLOCK => $this->state_info->isActionAvailable(Action::UNBLOCK),
            Command::PUBLISH => $this->state_info->isActionAvailable(Action::PUBLISH),
            Command::WITHDRAW, Command::CONFIRM_WITHDRAW => $this->state_info->isActionAvailable(Action::WITHDRAW),
            Command::SUBMIT => $this->state_info->isActionAvailable(Action::SUBMIT),
            Command::ACCEPT, Command::CONFIRM_ACCEPT => $this->state_info->isActionAvailable(Action::ACCEPT),
            Command::REJECT, Command::CONFIRM_REJECT => $this->state_info->isActionAvailable(Action::REJECT)
        };
    }

    protected function view(): void
    {
        $content = $this->content_factory->getInfoContent($this->ref_id, $this->obj_id, $this->type, $this->state_info);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function block(): void
    {
        $this->state_changer->block($this->obj_id);
        $this->showSuccessMessageAfterRedirect(Action::BLOCK);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function unblock(): void
    {
        $this->state_changer->unblock($this->obj_id);
        $this->showSuccessMessageAfterRedirect(Action::UNBLOCK);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function publish(): void
    {
        $this->state_changer->publish($this->obj_id, $this->type);
        $this->showSuccessMessageAfterRedirect(Action::PUBLISH);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function withdraw(): void
    {
        $content = $this->content_factory->getConfirmationContent(
            $this->ref_id,
            $this->obj_id,
            $this->type,
            Action::WITHDRAW,
            $this->object_handler->isOnlyReference($this->ref_id)
        );
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmWithdraw(): void
    {
        $this->state_changer->withdraw($this->obj_id);

        $this->showSuccessMessageAfterRedirect(Action::WITHDRAW);
        $link = $this->getRedirectTargetAfterDeletion($this->publishing_settings->getContainerRefIDForPublishing());
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($link));
        exit;
    }

    protected function submit(): void
    {
        $this->state_changer->submit($this->obj_id);
        $this->showSuccessMessageAfterRedirect(Action::SUBMIT);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function accept(): void
    {
        $content = $this->content_factory->getConfirmationContent(
            $this->ref_id,
            $this->obj_id,
            $this->type,
            Action::ACCEPT,
            $this->object_handler->isOnlyReference($this->ref_id)
        );
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmAccept(): void
    {
        $this->state_changer->accept($this->obj_id, $this->type);

        $this->showSuccessMessageAfterRedirect(Action::ACCEPT);
        $link = $this->getRedirectTargetAfterDeletion($this->publishing_settings->getContainerRefIDForEditorialStep());
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($link));
        exit;
    }

    protected function reject(): void
    {
        $content = $this->content_factory->getConfirmationContent(
            $this->ref_id,
            $this->obj_id,
            $this->type,
            Action::REJECT,
            $this->object_handler->isOnlyReference($this->ref_id)
        );
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmReject(): void
    {
        $this->state_changer->reject($this->obj_id);

        $this->showSuccessMessageAfterRedirect(Action::REJECT);
        $link = $this->getRedirectTargetAfterDeletion($this->publishing_settings->getContainerRefIDForEditorialStep());
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($link));
        exit;
    }

    protected function getRedirectTargetAfterDeletion(int $fallback_ref_id): URI
    {
        if ($this->object_handler->doesReferenceExist($this->ref_id)) {
            return $this->link_to_parent;
        }
        return $this->static_url->builder()->build('cat', $this->data_factory->refId($fallback_ref_id));
    }

    protected function showSuccessMessageAfterRedirect(Action $action): void
    {
        $this->tpl->setOnScreenMessage(
            GlobalTemplate::MESSAGE_TYPE_SUCCESS,
            $this->content_factory->getSuccessMessage($action),
            true
        );
    }
}
