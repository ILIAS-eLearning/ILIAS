<?php

namespace ILIAS\OrgUnit\Provider;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ilObjOrgUnit;
use ilObjOrgUnitGUI;
use ilOrgUnitExplorerGUI;
use ilTree;

/**
 * Class OrgUnitToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_ORGU_TREE = 'show_orgu_tree';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->administration();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];

        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_ORGU_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };

            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Tree")
                ->withContent($l($this->getTree()));
        }

        return $tools;
    }


    /**
     * @param int $ref_id
     *
     * @return string
     */
    private function getTree() : string
    {
        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", ilObjOrgUnitGUI::class, "showTree", new ilTree(1));
        $tree->setTypeWhiteList($this->getTreeWhiteList());
        $tree->setNodeOpen(ilObjOrgUnit::getRootOrgRefId());
        $tree->handleCommand();

        return $tree->getHTML();
    }


    private function getTreeWhiteList() : array
    {
        $whiteList = array("orgu");
        $pls = \ilOrgUnitExtension::getActivePluginIdsForTree();

        return array_merge($whiteList, $pls);
    }
}
