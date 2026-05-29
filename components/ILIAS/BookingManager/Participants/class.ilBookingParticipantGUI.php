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

use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\StandardGUIRequest;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Participant\ParticipantRepository;
use ILIAS\BookingManager\Participant\Table\ParticipantTable;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

/**
 * @ilCtrl_Calls ilBookingParticipantGUI: ilRepositorySearchGUI
 */
class ilBookingParticipantGUI
{
    public const PARTICIPANT_VIEW = 1;
    protected AccessManager $access;
    protected StandardGUIRequest $book_request;

    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected int $ref_id;
    protected int $pool_id;

    private readonly Refinery $refinery;
    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly HttpService $http;
    private readonly ilUIService $ui_service;
    private readonly Factory $data_factory;
    private readonly ParticipantRepository $participant_repository;

    public function __construct(ilObjBookingPoolGUI $a_parent_obj)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->bookingManager()->internal()->domain()->access();
        $this->toolbar = $DIC->toolbar();
        $this->book_request = $DIC
            ->bookingManager()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = new HttpService($DIC->http(), $this->refinery);
        $this->ui_service = $DIC->uiService();
        $this->data_factory = new Factory();
        $this->participant_repository = new ParticipantRepository($DIC->database());

        $this->ref_id = $a_parent_obj->getRefId();
        $this->pool_id = $a_parent_obj->getObject()->getId();

        $this->lng->loadLanguageModule('book');
        $this->lng->loadLanguageModule('exc');
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilRepositorySearchGUI::class):
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->addUserAccessFilterCallable(
                    fn(array $a_user_id): array => $this->access->filterManageableParticipants($this->ref_id, $a_user_id)
                );
                $rep_search->setTitle($this->lng->txt('book_add_participant'));
                $rep_search->setCallback($this, 'addParticipantObject');
                $this->ctrl->setReturn($this, 'render');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ctrl->getCmd('render');
                if (method_exists($this, $cmd)) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function executeTableAction(): void
    {
        $this->configureParticipantTable()->execute($this->getTableActionUrlBuilder());
        $this->render();
    }

    public function render(): void
    {
        if (!$this->access->canManageParticipants($this->ref_id)) {
            return;
        }

        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            [
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->ref_id
            ]
        );

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->configureParticipantTable()->getComponents($this->getTableActionUrlBuilder())
            )
        );
    }

    public function addUserFromAutoCompleteObject(): bool
    {
        if (trim($this->book_request->getUserLogin()) === '') {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('msg_no_search_string')
            );
            $this->render();
            return false;
        }

        $user_ids = [];
        foreach (explode(',', $this->book_request->getUserLogin()) as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                    $this->lng->txt('user_not_known')
                );
                $this->render();
            }

            $user_ids[] = $user_id;
        }

        return $this->addParticipantObject($user_ids);
    }

    /**
     * @param int[] $a_user_ids
     */
    public function addParticipantObject(array $a_user_ids): bool
    {
        foreach ($a_user_ids as $user_id) {
            if (ilObject::_lookupType($user_id) === 'usr') {
                $participant_obj = new ilBookingParticipant($user_id, $this->pool_id);

                if ($participant_obj->getIsNew()) {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                        $this->lng->txt('book_participant_assigned'),
                        true
                    );
                    continue;
                }

                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                    $this->lng->txt('book_participant_already_assigned'),
                    true
                );
                continue;
            }

            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                'dummy error message, change me'
            );
            return false;
        }

        $this->ctrl->redirect($this, 'render');
        return true;
    }

    public function assignObjects(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

        $table = new ilBookingAssignObjectsTableGUI($this, 'assignObjects', $this->ref_id, $this->pool_id);
        $this->tpl->setContent($table->getHTML());
    }


    private function configureParticipantTable(): ParticipantTable
    {
        return new ParticipantTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->http,
            $this->ui_service,
            $this->ctrl,
            $this->tpl,
            $this->refinery,
            $this->access,
            $this->participant_repository,
            $this->ref_id,
            $this->pool_id,
        );
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        return new URLBuilder($this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'executeTableAction')
        ));
    }
}
