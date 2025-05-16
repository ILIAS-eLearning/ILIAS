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

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilSearchControllerGUI: ilSearchGUI
 * @ilCtrl_Calls ilSearchControllerGUI: ilLuceneSearchGUI ilLuceneUserSearchGUI
 */
class ilSearchControllerGUI implements ilCtrlBaseClassInterface
{
    public const int TYPE_USER_SEARCH = -1;
    protected ilObjUser $user;

    protected ilCtrl $ctrl;
    protected ILIAS $ilias;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilRbacSystem $system;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->ilias = $DIC['ilias'];
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->system = $DIC->rbac()->system();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->user = $DIC->user();
    }

    public function executeCommand(): void
    {
        // Check hacks
        if (!$this->system->checkAccess('search', ilSearchSettings::_getSearchSettingRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            case 'illucenesearchgui':
                $this->ctrl->forwardCommand(new ilLuceneSearchGUI());
                break;

            case 'illuceneusersearchgui':
                if ($this->user->getId() === ANONYMOUS_USER_ID) {
                    $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
                }
                $this->ctrl->forwardCommand(new ilLuceneUserSearchGUI());
                break;

            case 'ilsearchgui':
            default:
                $search_gui = new ilSearchGUI();
                $this->ctrl->forwardCommand($search_gui);
                break;
        }
        $this->tpl->printToStdout();
    }
}
