<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Repository Explorer
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilConditionSelector extends ilRepositorySelectorExplorerGUI
{
    protected ?int $highlighted_parent = null;
    protected ?int $ref_id = null;

    /**
     * @inheritDoc
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_selection_gui = null,
        string $a_selection_cmd = "add",
        string $a_selection_par = "source_id"
    ) {
        parent::__construct(
            $a_parent_obj,
            $a_parent_cmd,
            $a_selection_gui,
            $a_selection_cmd,
            $a_selection_par
        );

        $this->setAjax(true);
    }

    /**
     * @inheritDoc
     */
    public function isNodeVisible($a_node): bool
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        if (!$ilAccess->checkAccess('read', '', (int) $a_node["child"])) {
            return false;
        }
        //remove childs of target object
        if ($tree->getParentId((int) $a_node["child"]) === $this->getRefId()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isNodeClickable($a_node): bool
    {
        if (!parent::isNodeClickable($a_node)) {
            return false;
        }

        if ($a_node["child"] == $this->getRefId()) {
            return false;
        }

        return true;
    }

    public function setRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;

        //can target object be highlighted?
        $target_type = ilObject::_lookupType($a_ref_id, true);

        if (!in_array($target_type, $this->getTypeWhiteList(), true)) {
            $this->highlighted_parent = $this->tree->getParentId($a_ref_id);
        }
    }

    public function getRefId(): ?int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function isNodeHighlighted($a_node): bool
    {
        //highlight parent if target object cant be highlighted
        if ($this->highlighted_parent == $a_node["child"]) {
            return true;
        }

        return parent::isNodeHighlighted($a_node);
    }
}
