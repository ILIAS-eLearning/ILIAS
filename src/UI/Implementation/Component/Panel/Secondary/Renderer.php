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
 
namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Render\Template as Template;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(C\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof C\Panel\Secondary\Listing) {
            return $this->renderListing($component, $default_renderer);
        } elseif ($component instanceof C\Panel\Secondary\Legacy) {
            return $this->renderLegacy($component, $default_renderer);
        }
        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderListing(C\Panel\Secondary\Listing $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.secondary.html", true, true);

        $tpl = $this->parseHeader($component, $default_renderer, $tpl);

        foreach ($component->getItemGroups() as $group) {
            if ($group instanceof C\Item\Group) {
                $tpl->setCurrentBlock("group");
                $tpl->setVariable("ITEM_GROUP", $default_renderer->render($group));
                $tpl->parseCurrentBlock();
            }
        }

        $tpl = $this->parseFooter($component, $default_renderer, $tpl);

        return $tpl->get();
    }

    protected function renderLegacy(C\Panel\Secondary\Legacy $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.secondary.html", true, true);

        $tpl = $this->parseHeader($component, $default_renderer, $tpl);

        $tpl->setCurrentBlock("legacy");
        $tpl->setVariable("BODY_LEGACY", $default_renderer->render($component->getLegacyComponent()));
        $tpl->parseCurrentBlock();

        $tpl = $this->parseFooter($component, $default_renderer, $tpl);

        return $tpl->get();
    }

    protected function parseHeader(
        C\Panel\Secondary\Secondary $component,
        RendererInterface $default_renderer,
        Template $tpl
    ) : Template {
        $title = $component->getTitle();
        $actions = $component->getActions();
        $view_controls = $component->getViewControls();

        if ($title != "" || $actions || $view_controls) {
            $tpl->setVariable("TITLE", $title);
            if ($actions) {
                $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
            }
            if ($view_controls) {
                foreach ($view_controls as $view_control) {
                    $tpl->setCurrentBlock("view_controls");
                    $tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
                    $tpl->parseCurrentBlock();
                }
            }
            $tpl->setCurrentBlock("heading");
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    protected function parseFooter(
        C\Panel\Secondary\Secondary $component,
        RendererInterface $default_renderer,
        Template $tpl
    ) : Template {
        $footer = $component->getFooter();

        if ($footer) {
            $tpl->setCurrentBlock("footer");
            $tpl->setVariable("FOOTER", $default_renderer->render($footer));
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return array(C\Panel\Secondary\Listing::class, C\Panel\Secondary\Secondary::class);
    }
}
