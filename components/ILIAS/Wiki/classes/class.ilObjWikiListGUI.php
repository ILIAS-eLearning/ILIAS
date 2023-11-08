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
 * ListGUI class for wiki objects.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWikiListGUI extends ilObjectListGUI
{
    protected string $child_id;

    /**
    * initialisation
    */
    public function init(): void
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

    public function getCommandFrame(string $cmd): string
    {
        switch ($cmd) {
            default:
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
        }

        return $frame;
    }

    public function getProperties(): array
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
                $mem = array_map(static function ($id): string {
                    $name = ilObjUser::_lookupName($id);
                    return $name["firstname"] . " " . $name["lastname"];
                }, $team->getMembers());
                $props[] = array("alert" => false, "property" => $lng->txt("wiki_team_members"),
                    "value" => implode(", ", $mem));
            }
        }


        return $props;
    }

    public function getCommandLink(string $cmd): string
    {
        switch ($cmd) {
            case 'downloadFile':
                $cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI" .
                    "&amp;cmdClass=ilwikipagegui&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=downloadFile&amp;file_id=" . $this->getChildId();
                break;

            default:
                // separate method for this line
                $cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
                break;
        }
        return $cmd_link;
    }

    public function setChildId(string $a_child_id): void
    {
        $this->child_id = $a_child_id;
    }

    public function getChildId(): string
    {
        return $this->child_id;
    }
}
