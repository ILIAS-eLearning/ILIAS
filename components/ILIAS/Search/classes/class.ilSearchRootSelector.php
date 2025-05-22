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

declare(strict_types=1);
/**
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package core
*/
class ilSearchRootSelector extends ilExplorer
{
    protected ilCtrl $ctrl;
    protected ilRbacSystem $system;

    private string $selectable_type = '';
    private int $ref_id = 0;
    private string $target_class = '';
    private array $clickable_types = [];
    private string $cmd = '';
    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->system = $DIC->rbac()->system();

        parent::__construct($a_target);
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("search_root_expand");

        // add here all container objects
        $this->addFilter("root");
        $this->addFilter("cat");
        $this->addFilter("grp");
        $this->addFilter("fold");
        $this->addFilter("crs");
        $this->setClickableTypes(array("root", "cat", "grp", "fold", "crs"));

        $this->setFiltered(true);
        $this->setFilterMode(IL_FM_POSITIVE);

        $this->setTitleLength(ilObject::TITLE_LENGTH);
    }

    public function setPathOpen(int $ref_id): void
    {
        if (count($this->expanded) <= 1) {
            foreach ($this->tree->getPathId($ref_id) as $id) {
                $this->setExpand($id);
            }
        }
    }

    public function setClickableTypes(array $a_types): void
    {
        $this->clickable_types = $a_types;
    }

    public function isClickable(string $type, int $ref_id = 0): bool
    {
        return (in_array($type, $this->clickable_types));
    }

    public function setTargetClass(string $a_class): void
    {
        $this->target_class = $a_class;
    }
    public function getTargetClass(): string
    {
        return $this->target_class ?: 'ilsearchgui';
    }
    public function setCmd(string $a_cmd): void
    {
        $this->cmd = $a_cmd;
    }
    public function getCmd(): string
    {
        return $this->cmd ?: 'selectRoot';
    }

    public function setSelectableType(string $a_type): void
    {
        $this->selectable_type = $a_type;
    }
    public function getSelectableType(): string
    {
        return $this->selectable_type;
    }
    public function setRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }
    public function getRefId(): int
    {
        return $this->ref_id;
    }


    public function buildLinkTarget($a_node_id, string $a_type): string
    {
        $this->ctrl->setParameterByClass($this->getTargetClass(), "root_id", $a_node_id);

        return $this->ctrl->getLinkTargetByClass($this->getTargetClass(), $this->getCmd());
    }

    public function buildFrameTarget(string $a_type, $a_child = 0, $a_obj_id = 0): string
    {
        return '';
    }

    public function showChilds($a_parent_id): bool
    {
        if ($a_parent_id == 0) {
            return true;
        }

        if ($this->system->checkAccess("read", (int) $a_parent_id)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * @inheritDoc
     */
    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option): void
    {
        #$tpl = new ilTemplate("tpl.tree.html", true, true, "components/ILIAS/UIComponent/Explorer");

        if (in_array("root", $this->clickable_types)) {
            $tpl->setCurrentBlock("link");
            //$tpl->setVariable("LINK_NAME",$lng->txt('repository'));

            $this->ctrl->setParameterByClass($this->getTargetClass(), 'root_id', ROOT_FOLDER_ID);
            $tpl->setVariable("LINK_TARGET", $this->ctrl->getLinkTargetByClass($this->getTargetClass(), $this->getCmd()));
            $tpl->setVariable("TITLE", $this->lng->txt("repository"));

            $tpl->parseCurrentBlock();
        }
    }
}
