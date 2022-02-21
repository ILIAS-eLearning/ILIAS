<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Toast;

use ilException;
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

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Toast\Toast::class
        ];
    }
}
