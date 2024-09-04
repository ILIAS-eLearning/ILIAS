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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Test\Participants\ParticipantFilter;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Participants\ParticipantTable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Test\Participants\ParticipantTableExtraTimeAction;
use ILIAS\Test\Participants\ParticipantTableFinishTestAction;
use ILIAS\Test\Participants\ParticipantTableIpRangeAction;
use ILIAS\Test\Participants\UI\ParticipantUIService;
use ILIAS\Test\TestDIC;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\URLBuilder;

/**
 * Class ilTestParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestParticipantsTableGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestEvaluationGUI
 */
class ilTestParticipantsGUI
{
    public const CMD_SHOW = 'show';

    public const CALLBACK_ADD_PARTICIPANT = 'addParticipants';

    protected ilTestObjectiveOrientedContainer $objective_parent;
    protected ilTestAccess $test_access;

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;

    public function __construct(
        protected ilObjTest $test_obj,
        protected ilTestQuestionSetConfig $question_set_config,
        protected ilAccess $access,
        protected ilGlobalTemplateInterface $main_tpl,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilUIService $ui_service,
        protected DataFactory $data_factory,
        protected ilLanguage $lng,
        protected ilCtrlInterface $ctrl,
        protected ilDBInterface $db,
        protected ilTabsGUI $tabs,
        protected ilToolbarGUI $toolbar,
        protected RequestDataCollector $testrequest
    ) {
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($access);
    }

    public function getTestObj(): ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestObj(ilObjTest $test_obj): void
    {
        $this->test_obj = $test_obj;
    }

    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->question_set_config;
    }

    public function setQuestionSetConfig(ilTestQuestionSetConfig $question_set_config): void
    {
        $this->question_set_config = $question_set_config;
    }

    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
    {
        return $this->objective_parent;
    }

    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objective_parent): void
    {
        $this->objective_parent = $objective_parent;
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->test_access;
    }

    public function setTestAccess(ilTestAccess $test_access): void
    {
        $this->test_access = $test_access;
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilrepositorysearchgui':
                $gui = new ilRepositorySearchGUI();
                $gui->setCallback($this, self::CALLBACK_ADD_PARTICIPANT, []);

                $gui->addUserAccessFilterCallable($this->participant_access_filter->getManageParticipantsUserFilter(
                    $this->getTestObj()->getRefId()
                ));


                $this->ctrl->setReturnByClass(self::class, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);

                break;

            case "iltestevaluationgui":
                $gui = new ilTestEvaluationGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $gui->setTestAccess($this->getTestAccess());
                $this->tabs->clearTargets();
                $this->tabs->clearSubTabs();

                $this->ctrl->forwardCommand($gui);

                break;

            default:

                $command = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';
                $this->{$command}();
        }
    }

    public function showCmd(): void
    {
        $manually_user_entry_modal = $this->getManuallyEntryModal();
        $add_user_dropdown = $this->getAddUserDropdown($manually_user_entry_modal);

        $components = $this->getParticipantTable()->getComponents(
            $this->getTableActionUrlBuilder()
        );

        $this->toolbar->addComponent($add_user_dropdown);
        $this->main_tpl->setContent(
            $this->ui_renderer->render(array_merge(
                $components,
                [$manually_user_entry_modal]
            ))
        );
    }

    public function executeTableActionCmd(): void
    {
        $this->getParticipantTable()->execute($this->getTableActionUrlBuilder());
    }

    private function getManuallyEntryModal(): RoundTrip|Standard
    {
        $ajax_url = $this->ctrl->getLinkTargetByClass(
            [ilTestParticipantsGUI::class, ilRepositorySearchGUI::class],
            'doUserAutoComplete',
            '',
            true
        );

        return $this->ui_factory->modal()->roundtrip(
            'manual_entry',
            [],
            [
                $this->ui_factory->input()->field()->tag('async_tags', [])
                    ->withSuggestionsStartAfter(2)
                    ->withUserCreatedTagsAllowed(false)
                    ->withAdditionalOnLoadCode($this->getAsyncAutocompleteOnLoadCode($ajax_url))
            ],
            '#'
        )->withSubmitLabel($this->lng->txt('add_users'));
    }

    private function getAddUserDropdown(Modal $manually_user_entry_modal): Dropdown
    {
        return $this->ui_factory->dropdown()->standard([
            $this->ui_factory->button()->shy(
                $this->lng->txt('manual_entry'),
                $manually_user_entry_modal->getShowSignal()
            ),
            $this->ui_factory->button()->shy(
                $this->lng->txt('paste'),
                $this->ctrl->getLinkTargetByClass(
                    [ilTestParticipantsGUI::class, ilRepositorySearchGUI::class],
                    'showClipboard'
                )
            ),
            $this->ui_factory->button()->shy(
                $this->lng->txt('search'),
                $this->ctrl->getLinkTargetByClass(
                    [ilTestParticipantsGUI::class, ilRepositorySearchGUI::class],
                    'start'
                )
            )
        ])->withLabel($this->lng->txt('add_users'));
    }

    private function getParticipantTable(): ParticipantTable
    {
        $test_dic = TestDIC::dic();

        return $test_dic['participant.table']
            ->withTableAction($test_dic['participant.action.ip_range'])
            ->withTableAction($test_dic['participant.action.extra_time'])
            ->withTableAction($test_dic['participant.action.finish_test'])
            ->withTestObject($this->getTestObj());
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        $uri = $this->ctrl->getLinkTargetByClass(ilTestParticipantsGUI::class, 'executeTableAction', "", true);
        return new URLBuilder($this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $uri));
    }

    private function getAsyncAutocompleteOnLoadCode(string $ajax_url): callable
    {
        return static function (string $id) use ($ajax_url) {
            return "
                const registerAutoload = (function() {
                    const ajax_url = '{$ajax_url}';
                    const dialog = document.getElementById('{$id}').closest('dialog');
                    const tagify = il.UI.Input.tagInput.getTagifyInstance('{$id}');
                    
                    tagify.settings.dropdown.appendTarget = dialog;
                    tagify.settings.dropdown.position = 'manual';
                    
                    tagify.on('input', onInput);
                    tagify.on('dropdown:show', (e, node) => {
                        tagify.DOM.scope.parentNode.appendChild(tagify.DOM.dropdown)
                    });
                    
                    let controller;
                    function onInput( e ){
                      var value = e.detail.value;
                      tagify.whitelist = null // reset the whitelist

                      if(value.trim().length <= 2) {
                        return;
                      }

                      controller && controller.abort('new input');
                      controller = new AbortController();
                    
                      // show loading animation.
                      tagify.loading(true);
                    
                      const url = new URL(window.location.protocol + '//' + window.location.host + '/' + ajax_url);
                      url.searchParams.set('term', value);
                      fetch(url, {signal:controller.signal})
                        .then(RES => RES.json())
                        .then(function(newWhitelist){
                          tagify.whitelist = newWhitelist.items.map((item) => ({
                            display: item.label,
                            value: item.value,
                            id: item.id,
                          }));
                          
                          // render the suggestions dropdown
                          tagify.loading(false).dropdown.show(value);
                        }).catch((error) => error)
                    }
                });
                // This is necessary because the the original tag input on load code is applied later in the rendering.
                // With the timeout the execution is put on the end of the event loop.
                setTimeout(registerAutoload, 0);
            ";
        };
    }

    public function addParticipants($user_ids = []): ?bool
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $filtered_user_ids = $filter_closure($user_ids);

        foreach ($filtered_user_ids as $user_id) {
            $user = new ilObjUser($user_id);
            $session = new ilTestSession($this->db, $user);
            $session->setUserId($user_id);
            $session->setTestId($this->getTestObj()->getTestId());
            $session->saveToDb();
        }

        return true;
    }


    //
    //    public function addParticipants($user_ids = []): ?bool
    //    {
    //        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
    //        $filtered_user_ids = $filter_closure($user_ids);
    //
    //        $countusers = 0;
    //        foreach ($filtered_user_ids as $user_id) {
    //            $client_ip = $_POST["client_ip"][$countusers] ?? '';
    //            $this->getTestObj()->inviteUser($user_id, $client_ip);
    //            $countusers++;
    //        }
    //
    //        $message = "";
    //        if ($countusers) {
    //            $message = $this->lng->txt("tst_invited_selected_users");
    //        }
    //        if (strlen($message)) {
    //            $this->main_tpl->setOnScreenMessage('info', $message, true);
    //        } else {
    //            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("tst_invited_nobody"), true);
    //            return false;
    //        }
    //
    //        $this->ctrl->redirect($this, self::CMD_SHOW);
    //        return null;
    //    }

    protected function saveClientIpCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $selected_users = $filter_closure($this->testrequest->raw('chbUser') ?? []);

        if ($selected_users === []) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        foreach ($selected_users as $user_id) {
            $this->getTestObj()->setClientIP($user_id, $_POST["clientip_" . $user_id]);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function removeParticipantsCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $a_user_ids = $filter_closure((array) $_POST["chbUser"]);

        if (is_array($a_user_ids)) {
            foreach ($a_user_ids as $user_id) {
                $this->getTestObj()->disinviteUser($user_id);
            }
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }


}
