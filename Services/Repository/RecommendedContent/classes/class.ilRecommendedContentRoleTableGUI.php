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
 * Recommended content for roles
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRecommendedContentRoleTableGUI extends ilTable2GUI
{
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilTree $tree;
    protected int $role_id;
    protected ilRecommendedContentManager $manager;

    public function __construct(
        ilRecommendedContentRoleConfigGUI $a_parent_obj,
        string $a_parent_cmd,
        int $role_id,
        ilRecommendedContentManager $manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();

        $this->role_id = $role_id;

        $this->setId('objrolepd');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('rep_recommended_content') .
            ', ' . $this->lng->txt("obj_role") . ': ' . ilObjRole::_getTranslation(ilObject::_lookupTitle($this->role_id)));

        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('path'));

        $this->setRowTemplate(
            "tpl.rec_content_list_role.html",
            "Services/Repository/RecommendedContent"
        );
        $this->setDefaultOrderField('title');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->addMultiCommand('confirmRemoveItems', $this->lng->txt('remove'));
        $this->setSelectAllCheckbox('del_desk_item');

        $this->manager = $manager;

        $this->getItems();
    }

    protected function getItems() : void
    {
        $tree = $this->tree;

        $data = array_map(function ($ref_id) use ($tree) {
            return [
                "ref_id" => $ref_id,
                "title" => ilObject::_lookupTitle(ilObject::_lookupObjectId($ref_id)),
                "path" => $this->formatPath($tree->getPathFull($ref_id))
            ];
        }, $this->manager->getRecommendationsOfRole($this->role_id));

        $this->setData($data);
    }

    protected function formatPath(array $a_path_arr) : string
    {
        return implode(" &raquo; ", array_column($a_path_arr, "title"));
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("VAL_ID", $a_set["ref_id"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_PATH", $a_set["path"]);
    }
}
