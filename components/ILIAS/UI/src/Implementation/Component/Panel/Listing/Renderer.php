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

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Implementation\Render\Template as Template;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Panel\HasExpandableRenderer;

class Renderer extends AbstractComponentRenderer
{
    use HasExpandableRenderer;

    /**
     * @inheritdoc
     */
    public function render(C\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof C\Panel\Listing\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        return '';
    }

    protected function renderStandard(C\Panel\Listing\Listing $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.listing_standard.html", true, true);
        $f = $this->getUIFactory();

        $id = $this->bindJavaScript($component);
        if ($id === null) {
            $id = $this->createId();
        }
        $tpl->setVariable("ID", $id);

        $tpl_heading = $this->parseExpandingHeader($component, $default_renderer, $f);
        $tpl->setVariable("HEADING", $tpl_heading->get());
        $tpl = $this->declareExpandable($component, $tpl);

        foreach ($component->getItemGroups() as $group) {
            if ($group instanceof Group) {
                $tpl->setCurrentBlock("group");
                $tpl->setVariable("ITEM_GROUP", $default_renderer->render($group));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/panel.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(C\Panel\Listing\Standard::class);
    }
}
