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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Content;

use ILIAS\MetaData\OERHarvester\ControlCenter\State\Status;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\UI\Component\Chart\ScaleBar;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Button\Button;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactoryInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;
use ILIAS\MetaData\Copyright\RepositoryInterface;

class ContentFactory implements ContentFactoryInterface
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected PresentationUtilities $presentation_utilities,
        protected LinkFactoryInterface $link_factory,
        protected RepositoryInterface $copyright_repository
    ) {
    }

    public function getInfoContent(
        int $ref_id,
        int $obj_id,
        string $type,
        StateInfoInterface $state_info
    ): RoundTripModal {
        $message = $this->getStatusMessage($state_info);
        $scale = $this->getStatusOverview($state_info);
        $actions = $this->getActions($ref_id, $obj_id, $type, $state_info);

        return $this->ui_factory->modal()->roundtrip(
            $this->presentation_utilities->txt('md_publishing_center_title'),
            [$message, $scale]
        )->withActionButtons($actions);
    }

    protected function getStatusOverview(StateInfoInterface $state_info): ScaleBar
    {
        $scale_items = [];
        foreach ($state_info->getAllPossibleStatuses() as $status) {
            $status_label = match ($status) {
                Status::UNPUBLISHED => $this->presentation_utilities->txt('md_publishing_status_unpublished'),
                Status::BLOCKED => $this->presentation_utilities->txt('md_publishing_status_blocked'),
                Status::UNDER_REVIEW => $this->presentation_utilities->txt('md_publishing_status_under_review'),
                Status::PUBLISHED => $this->presentation_utilities->txt('md_publishing_status_published')
            };
            $scale_items[$status_label] = ($status === $state_info->getCurrentStatus());
        }
        return $this->ui_factory->chart()->scaleBar($scale_items);
    }

    protected function getStatusMessage(StateInfoInterface $state_info): MessageBox
    {
        if (
            $state_info->getCurrentStatus() === Status::UNPUBLISHED &&
            !$state_info->hasEligibleCopyright()
        ) {
            $valid_cp = [];
            foreach ($state_info->getAllEligibleCopyrightEntryIDs() as $copyright_id) {
                $valid_cp[] = $this->copyright_repository->getEntry($copyright_id)->title();
            }
            $valid_cp_list = '<br/><br/>' . implode('<br/>', $valid_cp);
            $message = $this->presentation_utilities->txtFill('md_publishing_info_wrong_copyright', $valid_cp_list);
        } else {
            $message = match ($state_info->getCurrentStatus()) {
                Status::UNPUBLISHED => $this->presentation_utilities->txt('md_publishing_info_unpublished'),
                Status::BLOCKED => $this->presentation_utilities->txt('md_publishing_info_blocked'),
                Status::UNDER_REVIEW => $this->presentation_utilities->txt('md_publishing_info_under_review'),
                Status::PUBLISHED => $this->presentation_utilities->txt('md_publishing_info_published')
            };
        }

        return $this->ui_factory->messageBox()->info($message);
    }

    /**
     * @return Button[]
     */
    protected function getActions(
        int $ref_id,
        int $obj_id,
        string $type,
        StateInfoInterface $state_info
    ): array {
        $buttons = [];
        foreach ($state_info->getRelevantActions() as $action) {
            $link = $this->link_factory->getLinkForAction($action, $ref_id, $obj_id, $type);
            $label = match ($action) {
                Action::BLOCK => $this->presentation_utilities->txt('md_publishing_action_block'),
                Action::UNBLOCK => $this->presentation_utilities->txt('md_publishing_action_unblock'),
                Action::PUBLISH => $this->presentation_utilities->txt('md_publishing_action_publish'),
                Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_action_withdraw'),
                Action::SUBMIT => $this->presentation_utilities->txt('md_publishing_action_submit'),
                Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_action_accept'),
                Action::REJECT => $this->presentation_utilities->txt('md_publishing_action_reject')
            };
            $disabled = !$state_info->isActionAvailable($action);
            /*$buttons[] = $this->ui_factory->button()->standard($label, $link)
                                                    ->withUnavailableAction($disabled);*/
            // This is an ugly workaround because prompts don't work correctly
            $button = $this->ui_factory->button()->standard($label, '#')->withUnavailableAction($disabled);
            if (!$disabled) {
                $button = $button->withOnLoadCode(
                    fn($id) => "$('#$id').on('click', (e)=> {
                        let promptId = e.target.closest('.il-prompt').id;
                        il.UI.prompt.get(promptId).show('$link');
                    });"
                );
            }
            $buttons[] = $button;
        }
        return $buttons;
    }

    public function getConfirmationContent(
        int $ref_id,
        int $obj_id,
        string $type,
        Action $action,
        bool $is_last_reference
    ): RoundTripModal {
        $modal_content = [];
        $message = match ($action) {
            Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_confirmation_info_withdraw'),
            Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_confirmation_info_accept'),
            Action::REJECT => $this->presentation_utilities->txt('md_publishing_confirmation_info_reject'),
            default => ''
        };
        $modal_content[] = $this->ui_factory->messageBox()->confirmation($message);
        $title = match ($action) {
            Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_confirmation_withdraw'),
            Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_confirmation_accept'),
            Action::REJECT => $this->presentation_utilities->txt('md_publishing_confirmation_reject'),
            default => ''
        };

        if (
            ($action === Action::REJECT || $action === Action::WITHDRAW) &&
            $is_last_reference
        ) {
            $modal_content[] = $this->ui_factory->messageBox()->info(
                $this->presentation_utilities->txt('md_publishing_last_reference_info')
            );
        }

        $action = $this->link_factory->getLinkForConfirmationOfAction($action, $ref_id, $obj_id, $type);
        /*$button = $this->ui_factory->button()->standard(
            $this->presentation_utilities->txt('confirm'),
            $action
        );*/
        // This is an ugly workaround because prompts don't work correctly
        $button = $this->ui_factory->button()->standard($this->presentation_utilities->txt('confirm'), '#');
        $button = $button->withOnLoadCode(
            fn($id) => "$('#$id').on('click', (e)=> {
                        let promptId = e.target.closest('.il-prompt').id;
                        il.UI.prompt.get(promptId).show('$action');
                    });"
        );
        return $this->ui_factory->modal()->roundtrip($title, $modal_content)->withActionButtons([$button]);
    }

    public function getSuccessMessage(Action $action): string
    {
        return match ($action) {
            Action::BLOCK => $this->presentation_utilities->txt('md_publishing_success_block'),
            Action::UNBLOCK => $this->presentation_utilities->txt('md_publishing_success_unblock'),
            Action::PUBLISH => $this->presentation_utilities->txt('md_publishing_success_publish'),
            Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_success_withdraw'),
            Action::SUBMIT => $this->presentation_utilities->txt('md_publishing_success_submit'),
            Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_success_accept'),
            Action::REJECT => $this->presentation_utilities->txt('md_publishing_success_reject')
        };
    }
}
