<?php declare(strict_types=0);
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
            ilCourseObjectiveResultCache::getStatus(
                $this->user->getId(),
                $this->getContainerObject()->object->getId()
            ) != ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_NONE && ilCourseObjectiveResultCache::isSuggested(
                $this->user->getId(),
                $this->getContainerObject()->object->getId(),
                $this->obj_id
            )
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

        $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        switch (ilCourseObjectiveResultCache::getStatus(
            $this->user->getId(),
            $this->getContainerObject()->object->getId()
        )) {
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_NONE:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_status'));
                $this->tpl->setVariable(
                    'PROGRESS_ICON',
                    $icons->renderIconForStatus(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM)
                );
                break;

            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST:
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_PRETEST:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_pretest'));
                if (ilCourseObjectiveResultCache::isSuggested(
                    $this->user->getId(),
                    $this->getContainerObject()->object->getId(),
                    $this->obj_id
                )) {
                    $this->tpl->setVariable(
                        'PROGRESS_ICON',
                        $icons->renderIconForStatus(ilLPStatus::LP_STATUS_FAILED_NUM)
                    );
                } else {
                    $this->tpl->setVariable(
                        'PROGRESS_ICON',
                        $icons->renderIconForStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM)
                    );
                }
                break;

            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_FINISHED:
            case ilCourseObjectiveResult::IL_OBJECTIVE_STATUS_FINAL:
                $this->tpl->setVariable('TXT_PROGRESS_INFO', $this->lng->txt('crs_objective_result'));
                if (ilCourseObjectiveResultCache::isSuggested(
                    $this->user->getId(),
                    $this->getContainerObject()->object->getId(),
                    $this->obj_id
                )) {
                    $this->tpl->setVariable(
                        'PROGRESS_ICON',
                        $icons->renderIconForStatus(ilLPStatus::LP_STATUS_FAILED_NUM)
                    );
                } else {
                    $this->tpl->setVariable(
                        'PROGRESS_ICON',
                        $icons->renderIconForStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM)
                    );
                }
                break;

        }
        $this->tpl->parseCurrentBlock();
    }
}
