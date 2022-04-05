<?php declare(strict_types=0);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

/**
 * List gui for course objectives
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilCourseObjectiveListGUI extends ilObjectListGUI
{
    /**
     * @inheritDoc
     */
    public function init() : void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = false;
        $this->cut_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->progress_enabled = true;
        $this->type = "lobj";
        //$this->gui_class_name = "ilobjcoursegui";

        // general commands array
        $this->commands = array();
    }

    public function getObjectiveListItemHTML(
        int $a_ref_id,
        int $a_obj_id,
        string $a_title,
        string $a_description,
        bool $a_manage = false
    ) : string {
        $this->tpl = new ilTemplate(
            "tpl.container_list_item.html",
            true,
            true,
            "Services/Container"
        );
        $this->initItem($a_ref_id, $a_obj_id, ilObject::_lookupType($a_obj_id), $a_title, $a_description);

        $this->insertIconsAndCheckboxes();
        $this->insertTitle();
        $this->insertDescription();

        if (!$a_manage) {
            $this->insertProgressInfo();
        }
        $this->insertPositionField();

        // subitems
        $this->insertSubItems();

        // reset properties and commands
        $this->cust_prop = array();
        $this->cust_commands = array();
        $this->sub_item_html = array();
        $this->position_enabled = false;

        return $this->tpl->get();
    }

    /**
     * @inheritDoc
     */
    public function insertTitle() : void
    {
        if (
            ilCourseObjectiveResultCache::getStatus($this->user->getId(),
                $this->getContainerObject()->object->getId()) != ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_NONE and
            ilCourseObjectiveResultCache::isSuggested($this->user->getId(),
                $this->getContainerObject()->object->getId(), $this->obj_id)
        ) {
            $this->tpl->setVariable('DIV_CLASS', 'ilContainerListItemOuterHighlight');
        } else {
            $this->tpl->setVariable('DIV_CLASS', 'ilContainerListItemOuter');
        }

        if (!$this->getCommandsStatus()) {
            $this->tpl->setCurrentBlock("item_title");
            $this->tpl->setVariable("TXT_TITLE", $this->getTitle());
            $this->tpl->parseCurrentBlock();
            return;
        }
        $this->tpl->setCurrentBlock("item_title_linked");
        $this->tpl->setVariable("TXT_TITLE_LINKED", $this->getTitle());

        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getContainerObject()->object->getRefId());
        $this->ctrl->setParameterByClass("ilrepositorygui", "objective_details", $this->obj_id);
        $link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);

        $this->tpl->setVariable("HREF_TITLE_LINKED", $link);
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @inheritDoc
     */
    public function insertProgressInfo() : void
    {
        $this->lng->loadLanguageModule('trac');
        $this->tpl->setCurrentBlock('item_progress');

        switch (ilCourseObjectiveResultCache::getStatus($this->user->getId(),
            $this->getContainerObject()->object->getId())) {
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_NONE:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_status'));
                $this->tpl->setVariable('PROGRESS_TYPE_IMG', ilUtil::getImagePath('scorm/not_attempted.svg'));
                $this->tpl->setVariable('PROGRESS_ALT_IMG', $this->lng->txt('trac_no_attempted'));
                break;

            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST:
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_PRETEST:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_pretest'));
                if (ilCourseObjectiveResultCache::isSuggested($this->user->getId(),
                    $this->getContainerObject()->object->getId(), $this->obj_id)) {
                    $this->tpl->setVariable('PROGRESS_TYPE_IMG', ilUtil::getImagePath('scorm/failed.svg'));
                    $this->tpl->setVariable('PROGRESS_ALT_IMG', $this->lng->txt('trac_failed'));
                } else {
                    $this->tpl->setVariable('PROGRESS_TYPE_IMG', ilUtil::getImagePath('scorm/passed.svg'));
                    $this->tpl->setVariable('PROGRESS_ALT_IMG', $this->lng->txt('trac_passed'));
                }
                break;

            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_FINISHED:
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_FINAL:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_result'));
                if (ilCourseObjectiveResultCache::isSuggested($this->user->getId(),
                    $this->getContainerObject()->object->getId(), $this->obj_id)) {
                    $this->tpl->setVariable('PROGRESS_TYPE_IMG', ilUtil::getImagePath('scorm/failed.svg'));
                    $this->tpl->setVariable('PROGRESS_ALT_IMG', $this->lng->txt('trac_failed'));
                } else {
                    $this->tpl->setVariable('PROGRESS_TYPE_IMG', ilUtil::getImagePath('scorm/passed.svg'));
                    $this->tpl->setVariable('PROGRESS_ALT_IMG', $this->lng->txt('trac_passed'));
                }
                break;

        }
        $this->tpl->parseCurrentBlock();
    }
}
