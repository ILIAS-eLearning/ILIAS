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
 
namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\TriggeredSignal;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Tree\Node;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        $tpl_name = "tpl.node.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        $async = false;
        if ($component instanceof Node\AsyncNode && $component->getAsyncLoading()) {
            $tpl->setVariable("ASYNCURL", $component->getAsyncURL());
            $async = true;
        }

        $icon = $component->getIcon();
        $label = $component->getLabel();
        /** @var URI|null $link */
        $link = $component->getLink();

        if (null !== $link) {
            $linkAsString = $this->getRefinery()
                ->uri()
                ->toString()
                ->transform($link);

            $tpl->setVariable("LINK", $linkAsString);
            $tpl->setVariable("LABEL_LINKED", $label);
            if ($icon) {
                $tpl->setVariable("ICON_LINKED", $default_renderer->render($icon));
            }
            if ($component instanceof Node\Bylined && null !== $component->getByline()) {
                $tpl->setVariable('BYLINE_LINKED', $component->getByline());
            } elseif ($component instanceof Node\Paired && null !== $component->getValue()) {
                $tpl->setVariable('VALUE_LINKED', $component->getValue());
            }
        } else {
            $tpl->setCurrentBlock("node_without_link");
            $tpl->setVariable("LABEL", $label);
            if ($icon) {
                $tpl->setVariable("ICON", $default_renderer->render($icon));
            }
            if ($component instanceof Node\Bylined && null !== $component->getByline()) {
                $tpl->setVariable('BYLINE', $component->getByline());
            } elseif ($component instanceof Node\Paired && null !== $component->getValue()) {
                $tpl->setVariable('VALUE', $component->getValue());
            }
        }

        if ($component->isHighlighted()) {
            $tpl->touchBlock("highlighted");
        }

        /**
         * @var $component Node\Simple|Node\Bylined|Node\Paired
         */
        $triggered_signals = $component->getTriggeredSignals();
        if (count($triggered_signals) > 0) {
            $component = $this->triggerFurtherSignals($component, $triggered_signals);
        }

        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        $subnodes = $component->getSubnodes();

        if (count($subnodes) > 0 || $async) {
            $tpl->touchBlock("expandable");
            $tpl->setCurrentBlock("aria_expanded");
            if ($component->isExpanded()) {
                $tpl->setVariable("ARIA_EXPANDED", "true");
            } else {
                $tpl->setVariable("ARIA_EXPANDED", "false");
            }
            $tpl->parseCurrentBlock();

            $subnodes_html = $default_renderer->render($subnodes);
            $tpl->setVariable("SUBNODES", $subnodes_html);
        }

        if ($async || $link === null || count($subnodes) !== 0) {
            $tpl->touchBlock("role_item");
        } else {
            $tpl->touchBlock("role_none");
        }

        return $tpl->get();
    }

    /**
     * Relay signals (beyond expansion) to the node's js.
     *
     * @param TriggeredSignal[] $triggered_signals
     */
    protected function triggerFurtherSignals(
        Node\Node $component,
        array $triggered_signals
    ) : Component\JavaScriptBindable {
        $signals = [];
        foreach ($triggered_signals as $s) {
            /**
             * @var $s TriggeredSignal
             */
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }
        $signals = json_encode($signals);

        return $component->withAdditionalOnLoadCode(fn ($id) => "
			$('#$id > span').click(function(e){
				var node = $('#$id'),
					signals = $signals;

				for (var i = 0; i < signals.length; i++) {
					var s = signals[i];
					node.trigger(s.signal_id, s);
				}

				return false;
			});");
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return array(
            Node\Simple::class,
            Node\Bylined::class,
            Node\Paired::class
        );
    }
}
