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

declare(strict_types=0);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\DI\UIServices;
use ILIAS\Data\Factory as ilDataFactory;

/**
 * Class ilCourseParticipantsGroupsGUI
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCourseParticipantsGroupsGUI:
 */
class ilCourseParticipantsGroupsGUI
{
    private int $ref_id = 0;

    protected ilAccessHandler $access;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilErrorHandling $error;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectDataCache $objectDataCache;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    protected UIServices $ui_services;
    protected ilDataFactory $data_factory;
    protected ilTree $tree;
    protected ilUIService $ui_service;

    public function __construct($a_ref_id)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tree = $DIC->repositoryTree();
        $this->ui_services = $DIC->ui();
        $this->ui_service = $DIC->uiService();
        $this->data_factory = new ilDataFactory();

        $this->ref_id = $a_ref_id;
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
        }
        $cmd = $this->ctrl->getCmd();
        if (!$cmd) {
            $cmd = "show";
        }
        $this->$cmd();
    }

    public function show(): void
    {
        $data_retrieval = new ilCourseParticipantsGroupsTableDataRetrieval(
            $this,
            $this->tree,
            $this->access,
            $this->ui_services,
            $this->ref_id
        );
        $data_retrieval->init();
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI(
            $data_retrieval,
            $this->ui_services,
            $this->ui_service,
            $this->http,
            $this->refinery,
            $this->lng,
            $this->ctrl,
            $this->data_factory,
            $this->tpl,
            $this->access,
            $this->objectDataCache
        );
        $tbl_gui->handleCommands();
        $this->tpl->setContent($tbl_gui->getHTML());
    }
}
