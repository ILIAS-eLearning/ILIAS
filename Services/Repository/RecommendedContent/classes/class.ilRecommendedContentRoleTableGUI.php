<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Recommended content for roles
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @author <killing@leifos.com>
 */
class ilRecommendedContentRoleTableGUI extends ilTable2GUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    /**
     * @var \ilTree
     */
    protected $tree;

    /**
     * @var int
     */
    protected $role_id;

    /**
     * @var \ilRecommendedContentManager
     */
    protected $manager;

    public function __construct(
        ilRecommendedContentRoleConfigGUI $a_parent_obj,
        string $a_parent_cmd,
        int $role_id,
        \ilRecommendedContentManager $manager
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

        $this->addColumn('', '', 1);
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

    /**
     * Get items
     */
    protected function getItems()
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

    /**
     * @param array $a_path_arr
     * @return string
     */
    protected function formatPath(array $a_path_arr) : string
    {
        return implode(" &raquo; ", array_column($a_path_arr, "title"));
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable("VAL_ID", $a_set["ref_id"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_PATH", $a_set["path"]);
    }
}
