<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Link\Standard) {
			return $this->renderStandard($component, $default_renderer);
		}
		return "";
	}

	protected function renderStandard(Component\Link\Standard $component, RendererInterface $default_renderer) {

		$tpl_name = "tpl.standard.html";

		$tpl = $this->getTemplate($tpl_name, true, true);
		$action = $component->getAction();
		$label = $component->getLabel();
		// The action is always put in the data-action attribute to have it available
		// on the client side, even if it is not available on rendering.
		$tpl->setVariable("ACTION", $action);
		$tpl->setVariable("LABEL", $label);
		$tpl->setVariable("HREF", $action);

		$this->maybeRenderId($component, $tpl);
		return $tpl->get();
	}

	protected function maybeRenderId(Component\Component $component, $tpl) {
		$id = $this->bindJavaScript($component);
		// Check if the button is acting as triggerer
		if ($component instanceof Component\Triggerer && count($component->getTriggeredSignals())) {
			$id = ($id === null) ? $this->createId() : $id;
			$this->triggerRegisteredSignals($component, $id);
		}
		if ($id !== null) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		(Component\Link\Standard::class
		);
	}
}
