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

use ILIAS\BookingManager\HttpService;
use ILIAS\BookingManager\Participant\ParticipantRepository;
use ILIAS\BookingManager\Participant\ParticipantTable;
use ILIAS\BookingManager\Participant\ParticipantTableBookForParticipantAction;
use ILIAS\BookingManager\Participant\ParticipantTableDeleteAction;
use ILIAS\BookingManager\Participant\ParticipantTableEditBookingAction;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

/**
 * Class ilBookingParticipantGUI
 * @author Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilBookingParticipantGUI: ilRepositorySearchGUI
 */
class ilBookingParticipantGUI
{
    public const PARTICIPANT_VIEW = 1;
    protected \ILIAS\BookingManager\Access\AccessManager $access;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;

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
    private readonly HttpService $http_service;
    private readonly ilUIService $ui_service;
    private readonly Factory $data_factory;
    private readonly ParticipantRepository $participant_repository;

    public function __construct(
        ilObjBookingPoolGUI $a_parent_obj
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->bookingManager()->internal()->domain()->access();
        $this->toolbar = $DIC->toolbar();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http_service = new HttpService($DIC->http(), $this->refinery);
        $this->ui_service = $DIC->uiService();
        $this->data_factory = new Factory();
        $this->participant_repository = new ParticipantRepository(
            $DIC->database()
        );

        $this->ref_id = $a_parent_obj->getRefId();
        $this->pool_id = $a_parent_obj->getObject()->getId();

        $this->lng->loadLanguageModule("book");
        $this->lng->loadLanguageModule("exc");
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();
                $ref_id = $this->ref_id;
                $rep_search->addUserAccessFilterCallable(function ($a_user_id) {
                    return $this->access->filterManageableParticipants(
                        $this->ref_id,
                        $a_user_id
                    );
                });
                $rep_search->setTitle($this->lng->txt("book_add_participant"));
                $rep_search->setCallback($this, 'addParticipantObject');
                $this->ctrl->setReturn($this, 'render');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
    }

    public function executeTableAction(): void
    {
        $this
            ->configureParticipantTable()
            ->execute($this->getTableActionUrlBuilder());

        $this->render();
    }

    /**
     * Render list of booking participants.
     */
    public function render(): void
    {
        if (!$this->access->canManageParticipants($this->ref_id)) {
            return;
        }
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            array(
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->ref_id
            )
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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_search_string'));
            $this->render();
            return false;
        }

        $users = explode(',', $this->book_request->getUserLogin());

        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_not_known'));
                $this->render();
            }
            $user_ids[] = $user_id;
        }

        return $this->addParticipantObject($user_ids);
    }

    /**
     * Add new participant
     * @param int[] $a_user_ids
     * @throws ilCtrlException
     */
    public function addParticipantObject(
        array $a_user_ids
    ): bool {
        foreach ($a_user_ids as $user_id) {
            if (ilObject::_lookupType($user_id) === "usr") {
                $participant_obj = new ilBookingParticipant($user_id, $this->pool_id);
                if ($participant_obj->getIsNew()) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("book_participant_assigned"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("book_participant_already_assigned"), true);
                }
            } else {
                $this->tpl->setOnScreenMessage('failure', "dummy error message, change me");
                return false;
            }
        }

        $this->ctrl->redirect($this, "render");
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
            $this->lng,
            new TableActions(
                $this->ctrl,
                $this->lng,
                $this->tpl,
                $this->ui_factory,
                $this->ui_renderer,
                $this->refinery,
                $this->http_service,
                [
                    ParticipantTableBookForParticipantAction::ACTION_ID => new ParticipantTableBookForParticipantAction(
                        $this->ui_factory,
                        $this->lng,
                        $this->access,
                        $this->ctrl,
                        $this->http_service,
                        $this->ref_id,
                        $this->pool_id
                    ),
                    ParticipantTableEditBookingAction::ACTION_ID => new ParticipantTableEditBookingAction(
                        $this->ui_factory,
                        $this->lng,
                        $this->access,
                        $this->ctrl,
                        $this->http_service,
                        $this->ref_id,
                        $this->pool_id
                    ),
                    ParticipantTableDeleteAction::ACTION_ID => new ParticipantTableDeleteAction(
                        $this->ui_factory,
                        $this->lng,
                        $this->access,
                        $this->tpl,
                        $this->http_service,
                        $this->participant_repository,
                        $this->ref_id,
                        $this->pool_id
                    ),
                ]
            ),
            $this->http_service,
            $this->ui_service,
            $this->pool_id,
            $this->http_service->getRequest()
        );
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        return new URLBuilder($this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                self::class,
                'executeTableAction'
            )
        ));
    }
}
