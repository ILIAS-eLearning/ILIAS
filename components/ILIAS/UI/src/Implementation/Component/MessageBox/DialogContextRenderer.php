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

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\MessageBox
 */
class DialogContextRenderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $ui_fac = $this->getUIFactory();

        if (!$component instanceof Component\MessageBox\MessageBox) {
            $this->cannotHandleComponent($component);
        }

        $tpl = $this->getTemplate("tpl.messagebox.html", true, true);
        $tpl->setCurrentBlock("message_box");

        $tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());
        $tpl->setVariable("ACC_TEXT", $this->txt($component->getType() . "_message"));
        if ($component->getType() == Component\MessageBox\MessageBox::FAILURE) {
            $tpl->setVariable("ROLE", "alert");
        } else {
            $tpl->setVariable("ROLE", "status");
        }


        $links = $component->getLinks();
        if (count($links) > 0) {
            $unordered = $ui_fac->listing()->unordered(
                $links
            );

            $tpl->setVariable("LINK_LIST", $default_renderer->render($unordered));
        }

        $tpl->touchBlock($component->getType() . "_class");

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
