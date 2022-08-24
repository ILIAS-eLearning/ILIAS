<?php

declare(strict_types=1);

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
 * @author Stefan Meyer <meyer@leifos.com>
 * @deprecated
 */
class ilContainerSelectionExplorer extends ilExplorer
{
    protected ilAccessHandler $access;
    protected string $target_type;

    public function __construct(
        string $a_target
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();

        parent::__construct($a_target);

        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("ref_repexpand");

        $this->addFilter("root");
        $this->addFilter("cat");
        $this->addFilter("grp");
        $this->addFilter("crs");

        $this->setFilterMode(IL_FM_POSITIVE);
        $this->setFiltered(true);
        $this->setTitleLength(ilObject::TITLE_LENGTH);

        $this->checkPermissions(true);
    }

    public function setTargetType(string $a_type): void
    {
        $this->target_type = $a_type;
    }

    public function getTargetType(): string
    {
        return $this->target_type;
    }

    public function isClickable(string $type, int $ref_id = 0): bool
    {
        return ($this->getTargetType() === $type && $this->access->checkAccess('visible', '', $ref_id));
    }

    public function isVisible($a_ref_id, string $a_type): bool
    {
        $ilAccess = $this->access;

        return $ilAccess->checkAccess('visible', '', $a_ref_id);
    }

    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option): void
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");// TODO PHP8-REVIEW Why is this overwritten? Are you sure this is correct?

        $tpl->setCurrentBlock("text");
        $tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
        $tpl->parseCurrentBlock();

        $this->output[] = $tpl->get();// TODO PHP8-REVIEW `$this->output` is a string, so this operator (`[]`) is not supported
    }
}
