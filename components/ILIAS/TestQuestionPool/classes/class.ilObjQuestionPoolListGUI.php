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

/**
* Class ilObjQuestionPoolListGUI
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup components\ILIASTestQuestionPool
 */
class ilObjQuestionPoolListGUI extends ilObjectListGUI
{
    protected $command_link_params = [];

    /**
    * constructor
    *
    */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);
    }

    /**
    * initialisation
    */
    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "qpl";
        $this->gui_class_name = "ilobjquestionpoolgui";

        $this->commands = ilObjQuestionPoolAccess::_getCommands();
    }



    /**
    * Get command target frame
    */
    public function getCommandFrame(string $cmd): string
    {
        $frame = '';
        switch ($cmd) {
            case "":
            case "questions":
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
        $props = [];

        if (!$this->object_properties->getPropertyIsOnline()->getIsOnline()) {
            $props[] = ["alert" => true, "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline")];
        }
        return $props;
    }


    /**
    * Get command link url.
    */
    public function getCommandLink(string $cmd): string
    {
        $cmd = explode('::', $cmd);

        if (count($cmd) == 2) {
            $cmd_link = $this->ctrl->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjQuestionPoolGUI', $cmd[0]], $cmd[1]);
        } else {
            $cmd_link = $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolGUI', $cmd[0]);
        }

        $params = array_merge(['ref_id' => $this->ref_id], $this->command_link_params);

        foreach ($params as $param => $value) {
            $cmd_link = ilUtil::appendUrlParameterString($cmd_link, "$param=$value", true);
        }

        return $cmd_link;
    }
} // END class.ilObjTestListGUI
