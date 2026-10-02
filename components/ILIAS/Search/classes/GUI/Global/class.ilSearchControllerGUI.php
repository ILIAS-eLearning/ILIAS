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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\Search\Service\Service;
use ILIAS\Search\GUI\Global\Object\Actions as ObjectSearchActions;
use ILIAS\Search\GUI\Global\User\Actions as UserSearchActions;
use ILIAS\Search\GUI\Global\AccessChecker;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilSearchControllerGUI: ilSearchGUI
 * @ilCtrl_Calls ilSearchControllerGUI: ilLuceneUserSearchGUI
 */
class ilSearchControllerGUI implements ilCtrlBaseClassInterface
{
    public const int TYPE_USER_SEARCH = -1;
    protected ilObjUser $user;

    protected ilCtrl $ctrl;
    protected ILIAS $ilias;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilRbacSystem $system;
    protected Factory $refinery;

    protected ilSearchSettings $settings;
    protected ObjectSearchActions $object_search_actions;
    protected UserSearchActions $user_search_actions;
    protected AccessChecker $access_checker;

    public function __construct()
    {
        global $DIC;

        $service = new Service($DIC);

        $this->ctrl = $DIC->ctrl();
        $this->ilias = $DIC['ilias'];
        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->system = $DIC->rbac()->system();
        $this->user = $DIC->user();

        $this->settings = ilSearchSettings::getInstance();
        $this->access_checker = $service->gui()->accessChecker();
        $this->object_search_actions = $service->gui()->objectSearchActions();
        $this->user_search_actions = $service->gui()->userSearchActions();
    }

    public function executeCommand(): void
    {
        $this->fillHeaderAndTabs();

        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            case 'illuceneusersearchgui':
                if (!$this->access_checker->canAccessUserSearch()) {
                    $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
                }
                $this->tabs->activateTab('search_tab_user');
                $this->ctrl->forwardCommand(new ilLuceneUserSearchGUI());
                break;

            case 'ilsearchgui':
            default:
                if (!$this->access_checker->canAccessObjectSearch()) {
                    $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
                }
                $this->tabs->activateTab('search');
                $search_gui = new ilSearchGUI();
                $this->ctrl->forwardCommand($search_gui);
                break;
        }
        $this->tpl->printToStdout();
    }

    protected function fillHeaderAndTabs(): void
    {
        $this->tpl->loadStandardTemplate();

        // tabs
        if ($this->access_checker->canAccessObjectSearch()) {
            $this->tabs->addTab(
                'search',
                $this->lng->txt('search_tab_content'),
                (string) $this->object_search_actions->showSavedResults()
            );
        }
        if ($this->access_checker->canAccessUserSearch()) {
            $this->tabs->addTarget(
                'search_tab_user',
                (string) $this->user_search_actions->showSavedResults()
            );
        }

        // help
        if ($this->settings->enabledLucene()) {
            $this->help->setScreenIdComponent('src_luc');
        } else {
            $this->help->setScreenIdComponent('src');

        }

        // header
        $this->tpl->setTitleIcon(
            ilObject::_getIcon(0, "big", "src"),
            ""
        );
        $this->tpl->setTitle($this->lng->txt("search"));
    }
}
