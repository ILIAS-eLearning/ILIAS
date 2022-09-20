<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjTestListGUI
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesTest
*/


include_once "Services/Object/classes/class.ilObjectListGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjTestListGUI extends ilObjectListGUI
{
    protected $command_link_params = array();

    /**
    * constructor
    *
    */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);
        $this->info_screen_enabled = true;
    }

    /**
    * initialisation
    */
    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->type = "tst";
        $this->gui_class_name = "ilobjtestgui";

        // general commands array
        include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
        $this->commands = ilObjTestAccess::_getCommands();
    }



    /**
    * Get command target frame
    *
    * @param	string		$cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame(string $cmd): string
    {
        $frame = '';
        switch ($cmd) {
            case "":
            case "infoScreen":
            case "eval_a":
            case "eval_stat":
                include_once "./Services/UICore/classes/class.ilFrameTargetInfo.php";
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
        }

        return $frame;
    }



    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties(): array
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $props = parent::getProperties();

        // we cannot use ilObjTestAccess::_isOffline() because of text messages
        $onlineaccess = ilObjTestAccess::_lookupOnlineTestAccess($this->obj_id, $ilUser->getId());
        if ($onlineaccess !== true) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $onlineaccess);
        }

        return $props;
    }


    /**
    * Get command link url.
    */
    public function getCommandLink(string $cmd): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $cmd = explode('::', $cmd);

        if (count($cmd) == 2) {
            $cmd_link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjTestGUI', $cmd[0]), $cmd[1]);
        } else {
            $cmd_link = $ilCtrl->getLinkTargetByClass('ilObjTestGUI', $cmd[0]);
        }

        $params = array_merge(array('ref_id' => $this->ref_id), $this->command_link_params);

        foreach ($params as $param => $value) {
            $cmd_link = ilUtil::appendUrlParameterString($cmd_link, "$param=$value", true);
        }

        return $cmd_link;
    }

    public function getCommands(): array
    {
        $commands = parent::getCommands();

        $commands = $this->handleUserResultsCommand($commands);

        return $commands;
    }

    private function handleUserResultsCommand($commands)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        if (!$this->isObjectiveTest()) {
            $commands = $this->removeUserResultsCommand($commands);
        } else {
            require_once 'Modules/Test/classes/class.ilObjTestAccess.php';

            if (!ilObjTestAccess::visibleUserResultExists($this->obj_id, $ilUser->getId())) {
                $commands = $this->removeUserResultsCommand($commands);
            }
        }

        return $commands;
    }

    private function isObjectiveTest(): bool
    {
        require_once 'Modules/Course/classes/Objectives/class.ilLOSettings.php';
        return ilLOSettings::isObjectiveTest($this->ref_id);
    }

    private function removeUserResultsCommand($commands)
    {
        foreach ($commands as $key => $command) {
            if ($command['cmd'] == 'userResultsGateway') {
                unset($commands[$key]);
                break;
            }
        }

        return $commands;
    }

    /**
     * overwritten from base class for course objectives
     *
     * @access public
     * @param
     * @return
     */
    public function createDefaultCommand(array $command): array
    {
        return $command;
    }


    /**
     * add command link parameters
     *
     * @access public
     * @param array (param => value)
     */
    public function addCommandLinkParameter($a_param)
    {
        $this->command_link_params = $a_param;
    }

    // begin-patch lok
    protected function modifyTitleLink(string $default_link): string
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $id = ilLOSettings::isObjectiveTest($this->ref_id);

        $cmd_link = $default_link;

        if ($id) {
            $ref_ids = ilObject::_getAllReferences($id);
            $ref_id = end($ref_ids);

            $this->ctrl->setParameterByClass("ilrepositorygui", 'ref_id', $ref_id);
            $this->ctrl->setParameterByClass("ilrepositorygui", 'tid', $this->ref_id);
            $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", 'redirectLocToTest');
            $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
            $this->ctrl->clearParametersByClass('ilrepositorygui');
        }
        return parent::modifyTitleLink($cmd_link);
    }
    // end-patch lok
} // END class.ilObjTestListGUI
