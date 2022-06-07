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
 
namespace ILIAS\UI\Implementation\Component\Toast;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Toast\Toast) {
            return $this->renderToast($component, $default_renderer);
        }
        if ($component instanceof Component\Toast\Container) {
            return $this->renderContainer($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderToast(Component\Toast\Toast $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.toast.html", true, true);

        $title = $component->getTitle();
        if ($title instanceof Shy || $title instanceof Link) {
            $title = $default_renderer->render($title);
        } else {
            $title = htmlentities($title);
        }
        $tpl->setVariable("TITLE", $title);

        $tpl->setVariable("TOAST_DELAY", $component->getDelayTime());
        $tpl->setVariable("TOAST_VANISH", $component->getVanishTime());
        $tpl->setVariable("VANISH_ASYNC", $component->getAction());

        $desc = htmlentities($component->getDescription());
        if (trim($desc) != "") {
            $tpl->setCurrentBlock("desc");
            $tpl->setVariable("DESC", $desc);
            $tpl->parseCurrentBlock();
        }

        $actions = $component->getLinks();
        if (!empty($actions)) {
            foreach ($actions as $action) {
                $tpl->setCurrentBlock("action");
                $tpl->setVariable("ACTION", $default_renderer->render($action));
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setVariable("ICON", $default_renderer->render($component->getIcon()));
        $tpl->setVariable("CLOSE", $default_renderer->render($this->getUIFactory()->button()->close()));

        $component = $component->withAdditionalOnLoadCode(fn ($id) => "
                il.UI.toast.setToastSettings($id);
                il.UI.toast.showToast($id);
            ");

        $tpl->setCurrentBlock("id");
        $tpl->setVariable('ID', $this->bindJavaScript($component));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function renderContainer(Component\Toast\Container $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.container.html", true, true);
        $tpl->setVariable("TOASTS", $default_renderer->render($component->getToasts()));
        return $tpl->get();
    }

    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Toast/toast.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Toast\Toast::class,
            Component\Toast\Container::class
        ];
    }
}
