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
 * ilForumMoveTopicsExplorer
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumMoveTopicsExplorer extends ilRepositorySelectorExplorerGUI
{
    protected int $current_frm_ref_id = 0;

    public function __construct(ilObjForumGUI $a_parent_obj, string $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTypeWhiteList(['root', 'cat', 'fold', 'crs', 'grp', 'frm']);
        $this->setSelectMode('frm_ref_id');
    }

    public function getCurrentFrmRefId() : int
    {
        return $this->current_frm_ref_id;
    }

    public function setCurrentFrmRefId(int $current_frm_ref_id) : void
    {
        $this->current_frm_ref_id = $current_frm_ref_id;
    }

    public function isNodeClickable($a_node) : bool
    {
        global $DIC;

        if ($a_node['type'] === 'frm') {
            if ($this->getCurrentFrmRefId() && $this->getCurrentFrmRefId() === $a_node['child']) {
                return false;
            }

            return $DIC->access()->checkAccess(
                'moderate_frm',
                '',
                (int) $a_node['child']
            ) && parent::isNodeClickable($a_node);
        }

        return false;
    }

    protected function isNodeSelectable($a_node) : bool
    {
        global $DIC;

        if ($a_node['type'] === 'frm') {
            if ($this->getCurrentFrmRefId() && $this->getCurrentFrmRefId() === $a_node['child']) {
                return false;
            }

            return $DIC->access()->checkAccess(
                'moderate_frm',
                '',
                (int) $a_node['child']
            ) && parent::isNodeSelectable($a_node);
        }

        return false;
    }
}
