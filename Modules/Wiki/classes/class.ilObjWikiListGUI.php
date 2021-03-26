<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * ListGUI class for wiki objects.
 *
 * @author 	Alex Killing <alex.killing@gmx.de>
 */
class ilObjWikiListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "wiki";
        $this->gui_class_name = "ilobjwikigui";
        
        // general commands array
        $this->commands = ilObjWikiAccess::_getCommands();
    }



    /**
    * Get command target frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        switch ($a_cmd) {
            default:
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
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
    public function getProperties()
    {
        $lng = $this->lng;

        $props = array();

        if (!ilObjWikiAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }

        $lng->loadLanguageModule("wiki");
        $info = ilExcRepoObjAssignment::getInstance()->getAssignmentInfoOfObj($this->ref_id, $this->user->getId());
        if (count($info) > 0) {
            $sub = ilExSubmission::getSubmissionsForFilename($this->ref_id, array(ilExAssignment::TYPE_WIKI_TEAM));
            foreach ($sub as $s) {
                $team = new ilExAssignmentTeam($s["team_id"]);
                $mem = array_map(function ($id) {
                    $name = ilObjUser::_lookupName($id);
                    return $name["firstname"] . " " . $name["lastname"];
                }, $team->getMembers());
                $props[] = array("alert" => false, "property" => $lng->txt("wiki_team_members"),
                    "value" => implode(", ", $mem));
            }
        }


        return $props;
    }


    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        switch ($a_cmd) {
            case 'downloadFile':
                $cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI" .
                    "&amp;cmdClass=ilwikipagegui&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=downloadFile&amp;file_id=" . $this->getChildId();
                break;
            
            default:
                // separate method for this line
                $cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$a_cmd";
                break;

        }
        

        return $cmd_link;
    }

    public function setChildId($a_child_id)
    {
        $this->child_id = $a_child_id;
    }
    public function getChildId()
    {
        return $this->child_id;
    }
} // END class.ilObjWikiListGUI
