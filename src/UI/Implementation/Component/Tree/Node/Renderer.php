<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Tree\Node;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		$tpl_name = "tpl.node.html";
		$tpl = $this->getTemplate($tpl_name, true, true);

		$async = false;
		if ($component instanceof Node\AsyncNode && $component->getAsyncLoading()) {
			$tpl->setVariable("ASYNCURL", $component->getAsyncURL());
			$async = true;
		}

		$label = $component->getLabel();
		/** @var URI|null $link */
		$link = $component->getLink();
		if (null !== $link) {
			$tpl->setCurrentBlock("node_with_link");

			global $DIC;
			$linkAsString = $DIC->refinery()->uri()->toString()->transform($link);
			$tpl->setVariable("LINK", $linkAsString);
		}
		else {
			$tpl->setCurrentBlock("node_without_link");
		}

		$tpl->setVariable("LABEL", $label);
		$tpl->parseCurrentBlock();

		if ($component instanceof Node\Bylined && null !== $component->getByline()) {
			$tpl->setVariable('BYLINE', $component->getByline());
		}

		$icon = $component->getIcon();
		if($icon){
			$tpl->setVariable("ICON", $default_renderer->render($icon));
		}

		if($component->isHighlighted()){
			$tpl->touchBlock("highlighted");
		}

		$triggered_signals = $component->getTriggeredSignals();
		if(count($triggered_signals) > 0) {
			$component = $this->triggerFurtherSignals($component, $triggered_signals);
		}

		$id = $this->bindJavaScript($component);
		$tpl->setVariable("ID", $id);

		$subnodes = $component->getSubnodes();

		if(count($subnodes) > 0 || $async) {
			$tpl->touchBlock("expandable");
			if($component->isExpanded()) {
				$tpl->touchBlock("expanded");
			}
		}

		if(count($subnodes) > 0) {
			$subnodes_html = $default_renderer->render($subnodes);
			$tpl->setVariable("SUBNODES", $subnodes_html);
		}

		return $tpl->get();
	}

	/**
	 * Relay signals (beyond expansion) to the node's js.
	 * @param Node\Node $component
	 * @param Signal[] $triggered_signals
	 */
	protected function triggerFurtherSignals(Node\Node $component, array $triggered_signals)
	{
		$signals = [];
		foreach ($triggered_signals as $s) {
			$signals[] = [
				"signal_id" => $s->getSignal()->getId(),
				"event" => $s->getEvent(),
				"options" => $s->getSignal()->getOptions()
			];
		}
		$signals = json_encode($signals);

		return $component->withAdditionalOnLoadCode(function ($id) use ($signals) {
			return "
			$('#$id > span').click(function(e){
				var node = $('#$id'),
					signals = $signals;

				for (var i = 0; i < signals.length; i++) {
					var s = signals[i];
					node.trigger(s.signal_id, s);
				}

				return false;
			});";
		});
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Node\Simple::class,
			Node\Bylined::class
		);
	}
}
