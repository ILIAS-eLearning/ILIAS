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

namespace ILIAS\Skill\Usage;

use ILIAS\Skill\Tree;
use ILIAS\UI;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class UsagesUI
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected Tree\SkillTreeManager $tree_manager;
    protected SkillUsageManager $usage_manager;
    protected \ilSkillTreeRepository $tree_repo;
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected array $usage = [];
    protected string $mode = "";

    public function __construct(
        string $cskill_id,
        array $usage,
        string $mode = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->usage_manager = $DIC->skills()->internal()->manager()->getUsageManager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();

        $id_parts = explode(":", $cskill_id);
        $this->skill_id = (int) $id_parts[0];
        $this->tref_id = (int) $id_parts[1];
        $this->usage = $usage;
        $this->mode = $mode;
    }

    public function render(): string
    {
        $tree = $this->tree_repo->getTreeForNodeId($this->skill_id);
        if ($this->mode === "tree") {
            $tree_obj = $this->tree_manager->getTree($tree->getTreeId());
            $title = $tree_obj->getTitle() . " > " . \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        } else {
            $title = \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        }

        //$description = $tree->getSkillTreePathAsString($skill_id, $tref_id);

        $listing = $this->getUsagesListing();

        $panel = $this->ui_fac->panel()->standard($title, $listing);

        return $this->ui_ren->render($panel);
    }

    protected function getUsagesListing(): UI\Component\Listing\CharacteristicValue\Text
    {
        $types = [];
        foreach ($this->usage as $type => $type_usages) {
            $types[$this->usage_manager->getTypeInfoString($type)] = count($type_usages) . " " .
                $this->usage_manager->getObjTypeString($type);
        }

        $listing = $this->ui_fac->listing()->characteristicValue()->text($types);

        return $listing;
    }
}
