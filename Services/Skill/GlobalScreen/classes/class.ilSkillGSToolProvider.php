<?php

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
    const SHOW_SKILL_TREE = 'show_skill_tree';
    const SHOW_TEMPLATE_TREE = 'show_template_tree';
    const SKILL_TREE_ID = 'skill_tree_id';

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


    /**
     * @return string
     */
    private function getSkillTree(int $tree_id) : string
    {
        $exp = new ilSkillTreeExplorerGUI(["ilAdministrationGUI", "ilObjSkillManagementGUI"], "showTree", $tree_id);

        return $exp->getHTML();
    }


    /**
     * @return string
     */
    private function getTemplateTree(int $tree_id) : string
    {
        $exp = new ilSkillTemplateTreeExplorerGUI(["ilAdministrationGUI", "ilObjSkillManagementGUI"], "showTree", $tree_id);

        return $exp->getHTML();
    }
}
