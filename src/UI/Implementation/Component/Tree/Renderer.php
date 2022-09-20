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

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Tree;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        /**
         * @var $component Tree\Expandable
         */

        $tpl_name = "tpl.tree.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        $tpl->setVariable("ARIA_LABEL", $component->getLabel());

        $nodes = [];
        foreach ($component->getData() as $record) {
            $nodes[] = $this->buildNode(
                $component->getRecursion(),
                $record,
                $component->getEnvironment()
            );
        }

        $nodes_html = $default_renderer->render($nodes);

        if ($component->isSubTree()) {
            return $nodes_html;
        }

        $tpl->setVariable('NODES', $nodes_html);

        $highlight_node_on_click = $component->getHighlightOnNodeClick();
        $component = $component->withAdditionalOnLoadCode(
            fn ($id) => "il.UI.tree.init('$id', $highlight_node_on_click)"
        );

        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);


        return $tpl->get();
    }

    /**
     * Trigger TreeRecursion::build and recurse into hierarchy by checking for
     * further children of the record.
     */
    protected function buildNode(
        Tree\TreeRecursion $recursion,
        $record,
        $environment
    ): Tree\Node\Node {
        $node = $recursion->build(
            $this->getUIFactory()->tree()->node(),
            $record,
            $environment
        );

        foreach ($recursion->getChildren($record, $environment) as $sub_record) {
            $node = $node->withAdditionalSubnode(
                $this->buildNode($recursion, $sub_record, $environment)
            );
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Tree/tree.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Tree\Expandable::class
        );
    }
}
