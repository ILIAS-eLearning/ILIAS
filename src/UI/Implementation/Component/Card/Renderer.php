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
 
namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Standard as StandardIcon;
use ILIAS\UI\Component\Button\Shy;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var Component\Card\Card $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.card.html", true, true);

        if ($component->getImage()) {
            $tpl->setVariable("IMAGE", $default_renderer->render($component->getImage()));
        }

        if ($component->isHighlighted()) {
            $tpl->touchBlock("highlight");
        } else {
            $tpl->touchBlock("no_highlight");
        }

        $title = $component->getTitle();
        $id = $this->bindJavaScript($component);
        if (!$id) {
            $id = $this->createId();
        }
        if (!empty($component->getTitleAction())) {
            if (is_string($component->getTitleAction())) {
                $tpl->setCurrentBlock("title_action_begin");
                $tpl->setVariable("HREF", $component->getTitleAction());
                $tpl->setVariable("ID", $id);
                $tpl->parseCurrentBlock();
            } elseif ($title instanceof Shy) {
                $title = $default_renderer->render($title);
            }
            if (is_array($component->getTitleAction())) {
                $tpl->setCurrentBlock("title_action_begin");
                $tpl->setVariable("ID", $id);
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setVariable("TITLE", $title);

        if (!empty($component->getTitleAction())) {
            $tpl->touchBlock("title_action_end");
        }

        if (is_array($component->getSections())) {
            foreach ($component->getSections() as $section) {
                $tpl->setCurrentBlock("section");
                $tpl->setVariable("SECTION", $default_renderer->render($section));
                $tpl->parseCurrentBlock();
            }
        }

        if ($component instanceof Component\Card\RepositoryObject) {
            $tpl->setCurrentBlock("action");

            $obj_icon = $component->getObjectIcon();
            if ($obj_icon !== null) {
                $tpl->setVariable("OBJECT_ICON", $default_renderer->render($obj_icon));
            }

            $progress = $component->getProgress();
            if ($progress !== null) {
                $tpl->setVariable("PROGRESS_STATUS", $default_renderer->render($progress));
            }

            $certificate = $component->getCertificateIcon();
            if ($certificate !== null) {
                $certificate_icon = new StandardIcon("cert", "Certificate", "medium", false);
                $tpl->setVariable("PROGRESS_STATUS", $default_renderer->render($certificate_icon));
            }

            $dropdown = $component->getActions();
            if ($dropdown !== null) {
                $tpl->setVariable("DROPDOWN", $default_renderer->render($dropdown));
            }

            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return array(Component\Card\Card::class);
    }
}
