<?php declare(strict_types=1);

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
 * Class ilObjLinkResourceListGUI
 * @author        Alex Killing <alex.killing@gmx.de>
 */
class ilObjLinkResourceListGUI extends ilObjectListGUI
{
    protected function getWebLinkRepo() : ilWebLinkRepository
    {
        return new ilWebLinkDatabaseRepository($this->obj_id);
    }

    public function getTitle() : string
    {
        if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !$this->getWebLinkRepo()->doesListExist()) {
            return ilObjLinkResourceAccess::_getFirstLink($this->obj_id)
                                          ->getTitle();
        }
        return parent::getTitle();
    }

    public function getDescription() : string
    {
        if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !$this->getWebLinkRepo()->doesListExist()) {
            $desc = ilObjLinkResourceAccess::_getFirstLink($this->obj_id)
                                           ->getDescription() ?? '';

            // #10682
            if ($this->settings->get("rep_shorten_description")) {
                $desc = ilStr::shortenTextExtended(
                    $desc,
                    (int) $this->settings->get(
                        "rep_shorten_description_length"
                    ),
                    true
                );
            }

            return $desc;
        }
        return parent::getDescription();
    }

    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->type = "webr";
        $this->gui_class_name = "ilobjlinkresourcegui";
        $this->info_screen_enabled = true;

        // general commands array
        $this->commands = ilObjLinkResourceAccess::_getCommands();
    }

    public function getCommandFrame(string $cmd) : string
    {
        // #16820 / #18419 / #18622
        if ($cmd == "" &&
            ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !$this->getWebLinkRepo()->doesListExist()) {
            $link = ilObjLinkResourceAccess::_getFirstLink($this->obj_id);

            // we could use the "internal" flag, but it would not work for "old" links
            if (!ilLinkInputGUI::isInternalLink($link->getTarget())) {
                return '_blank';
            }
        }
        return "";
    }

    public function getProperties() : array
    {
        return [];
    }

    public function getCommandLink(string $cmd) : string
    {
        $cmd_class = '';
        if ($this->request_wrapper->has('cmd_class')) {
            $cmd_class = $this->request_wrapper->retrieve(
                'cmdClass',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (
            $this->request_wrapper->has('wsp_id') ||
            strcasecmp($cmd_class, ilPersonalWorkspaceGUI::class) === 0
        ) {
            if (
                ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
                !$this->getWebLinkRepo()->doesListExist() &&
                $cmd == ''
            ) {
                $cmd = "calldirectlink";
            }
            $this->ctrl->setParameterByClass(
                $this->gui_class_name,
                "ref_id",
                ""
            );
            $this->ctrl->setParameterByClass(
                $this->gui_class_name,
                "wsp_id",
                $this->ref_id
            );
            return $this->ctrl->getLinkTargetByClass(
                array("ilpersonalworkspacegui", $this->gui_class_name),
                $cmd
            );
        } else {
            // separate method for this line
            switch ($cmd) {
                case '':
                    if (ilObjLinkResourceAccess::_checkDirectLink(
                        $this->obj_id
                    ) &&
                        !$this->getWebLinkRepo()->doesListExist()) {
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=calldirectlink";
                    } else {
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
                    }
                    break;

                default:
                    $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
            }
        }
        return $cmd_link;
    }
}
