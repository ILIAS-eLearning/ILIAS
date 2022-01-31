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
 ********************************************************************
 */

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Workspace GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilSkillGSToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_SKILL_TREE = 'show_skill_tree';
    public const SHOW_TEMPLATE_TREE = 'show_template_tree';
    public const SKILL_TREE_ID = 'skill_tree_id';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->desktop();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $lang = $this->dic->language();

        $lang->loadLanguageModule("skill");

        $title = $lang->txt("skmg_skills");

        $tools = [];

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_skmg.svg"), $title);


        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_SKILL_TREE, true)) {
            $tree_id = $additional_data->get(self::SKILL_TREE_ID);
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($l, $tree_id) {
                    return $l($this->getSkillTree($tree_id));
                });
        }

        $title = $lang->txt("skmg_skill_templates");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_skmg.svg"), $title);

        if ($additional_data->is(self::SHOW_TEMPLATE_TREE, true)) {
            $tree_id = $additional_data->get(self::SKILL_TREE_ID);
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Templates")
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($l, $tree_id) {
                    return $l($this->getTemplateTree($tree_id));
                });
        }
        return $tools;
    }

    private function getSkillTree(int $tree_id) : string
    {
        $exp = new ilSkillTreeExplorerGUI(["ilAdministrationGUI", "ilObjSkillManagementGUI",
                                           "SkillTreeAdminGUI", "ilObjSkillTreeGUI"], "showTree", $tree_id);

        return $exp->getHTML();
    }

    private function getTemplateTree(int $tree_id) : string
    {
        $exp = new ilSkillTemplateTreeExplorerGUI(["ilAdministrationGUI", "ilObjSkillManagementGUI",
                                                   "SkillTreeAdminGUI", "ilObjSkillTreeGUI"], "showTree", $tree_id);

        return $exp->getHTML();
    }
}
