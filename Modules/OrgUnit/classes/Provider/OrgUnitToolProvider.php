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

namespace ILIAS\OrgUnit\Provider;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Component\Tree\TreeRecursion;
use ilObjOrgUnit;
use ilObjOrgUnitGUI;
use ilOrgUnitExplorerGUI;
use ilOrgUnitExtension;
use ilTree;

/**
 * Class OrgUnitToolProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_ORGU_TREE = 'show_orgu_tree';

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->administration();
    }

    /**
     * @param CalledContexts $called_contexts
     * @return \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool[]
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];

        if ($called_contexts->current()->getAdditionalData()->is(self::SHOW_ORGU_TREE, true)) {
            $iff = function(string $id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };

            $t = function(string $key) : string {
                return $this->dic->language()->txt($key);
            };

            $tools[] = $this->factory->treeTool($iff('tree_new'))
                                     ->withTitle($t('tree'))
                                     ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard('orgu',
                                         'Orgu'))
                                     ->withTree($this->getTree());
        }

        return $tools;
    }

    private function getTree() : Tree
    {
        global $DIC;
        $lng = $DIC->language();
        $tree = $this->getTreeRecursion();

        $parent_node_id = $DIC->repositoryTree()->getParentId(ilObjOrgUnit::getRootOrgRefId());

        return $this->dic->ui()->factory()->tree()->expandable($lng->txt("org_units"), $tree)
                         ->withData($tree->getChildsOfNode($parent_node_id));
    }

    private function getTreeRecursion() : TreeRecursion
    {
        $tree = new ilOrgUnitExplorerGUI(
            "orgu_explorer",
            ilObjOrgUnitGUI::class,
            "showTree",
            new ilTree(1),
            $this->dic["ilAccess"]
        );
        $tree->setTypeWhiteList($this->getTreeWhiteList());
        $tree->setRootId(ilObjOrgUnit::getRootOrgRefId());
        $ref_id = (int)($_GET['item_ref_id'] ?? $_GET['ref_id'] ?? 0);
        if($ref_id !== 0) {
            $tree->setPathOpen((int)$ref_id);
        }
        $tree->setOrderField('title');

        return $tree;
    }

    /**
     * @return int[]
     */
    private function getTreeWhiteList() : array
    {
        $whiteList = array('orgu');
        $pls = ilOrgUnitExtension::getActivePluginIdsForTree();

        return array_merge($whiteList, $pls);
    }
}
