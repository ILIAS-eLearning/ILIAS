<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Button\Close) {
			return $this->renderClose($component);
		} else {
			return $this->renderButton($component, $default_renderer);
		}
	}

	protected function renderButton(Component\Button\Button $component, RendererInterface $default_renderer) {
		// TODO: Tt would be nice if we could use <button> for rendering a button
		// instead of <a>. This was not done atm, as there is no attribute on a
		// button to make it open an URL. This would require JS.

		if ($component instanceof Component\Button\Primary) {
			$tpl_name = "tpl.primary.html";
		}
		if ($component instanceof Component\Button\Standard) {
			$tpl_name = "tpl.standard.html";
		}
		if ($component instanceof Component\Button\Shy) {
			$tpl_name = "tpl.shy.html";
		}
		if ($component instanceof Component\Button\Tag) {
			$tpl_name = "tpl.tag.html";
		}

		$tpl = $this->getTemplate($tpl_name, true, true);
		$action = $component->getAction();
		// The action is always put in the data-action attribute to have it available
		// on the client side, even if it is not available on rendering.
		$tpl->setVariable("ACTION", $action);
		$label = $component->getLabel();
		if ($label !== null) {
			$tpl->setVariable("LABEL", $component->getLabel());
		}
		if ($component->isActive()) {
			$tpl->setCurrentBlock("with_href");
			$tpl->setVariable("HREF", $action);
			$tpl->parseCurrentBlock();
		} else {
			$tpl->touchBlock("disabled");
		}

		$this->maybeRenderId($component, $tpl);

		if ($component instanceof Component\Button\Tag) {

			$tpl->setVariable("CSSCLASSES", $component->getCSSClasses());

			$bgcol = $component->getBackgroundColor();
			if($bgcol) {
				$tpl->setVariable("BGCOL", $bgcol->asHex());
			}
			$forecol = $component->getForegroundColor();
			if($forecol) {
				$tpl->setVariable("FORECOL", $forecol->asHex());
			}
		}

		return $tpl->get();
	}


	protected function renderClose($component) {
		$tpl = $this->getTemplate("tpl.close.html", true, true);
		// This is required as the rendering seems to only create any output at all
		// if any var was set or block was touched.
		$tpl->setVariable("FORCE_RENDERING", "");
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
		(Component\Button\Primary::class
		, Component\Button\Standard::class
		, Component\Button\Close::class
		, Component\Button\Shy::class
		, Component\Button\Tag::class
		);
	}
}
