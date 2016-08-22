<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Button\Close) {
			return $this->render_close($component);
		}
		else {
			return $this->render_button($component, $default_renderer);
		}
	}

	protected function render_button(Component\Component $component, RendererInterface $default_renderer) {
		// TODO: Tt would be nice if we could use <button> for rendering a button
		// instead of <a>. This was not done atm, as there is no attribute on a
		// button to make it open an URL. This would require JS.

		if ($component instanceof Component\Button\Primary) {
			$tpl_name = "tpl.primary.html";
		}
		if ($component instanceof Component\Button\Standard) {
			$tpl_name = "tpl.standard.html";
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
		}
		else {
			$tpl->touchBlock("disabled");
		}

		$this->maybe_render_id($component, $tpl);

		return $tpl->get();
	}

	protected function render_close($component) {
		$tpl = $this->getTemplate("tpl.close.html", true, true);
		// This is required as the rendering seems to only create any output at all
		// if any var was set or block was touched.
		$tpl->setVariable("FORCE_RENDERING", "");
		$this->maybe_render_id($component, $tpl);
		return $tpl->get();
	}

	protected function maybe_render_id($component, $tpl) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array
			( Component\Button\Primary::class
			, Component\Button\Standard::class
			, Component\Button\Close::class
			);
	}
}
