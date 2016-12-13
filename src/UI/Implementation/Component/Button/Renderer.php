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
			return $this->renderClose($component);
		}
		else {
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

        $this->renderTriggeredComponents($component, $tpl, $default_renderer);
		$this->maybeRenderId($component, $tpl);
        return $tpl->get();
	}


    /**
     * Renders any components that are triggered by this button
     * // TODO This functionality does not belong here and should be available in general for any component rendering triggered components
     *
     * @param Component\Button\Button $button
     * @param $tpl
     * @param $default_renderer
     * @return Component\Button\Button
     */
	protected function renderTriggeredComponents(Component\Button\Button &$button, $tpl, $default_renderer) {
        if (!count($button->getTriggerActions())) {
            return $button;
        }
        foreach ($button->getTriggerActions() as $action) {
            $triggered = $action->getComponent();
            // Hacky: Fake generation of an ID via empty onload code
            $triggered = $triggered->withOnLoadCode(function($id) {
                return '';
            });
            $triggered_id = $this->bindJavaScript($triggered);
            $button = $button->withOnLoadCode(function($id) use ($triggered_id, $action) {
                $event = $action->getEvent();
                $binding = $action->getJavascriptBinding();
                $js = $binding($triggered_id);
                return "$('#{$id}').{$event}(function() { {$js} });";
            });
            $tpl->setCurrentBlock('triggered');
            $tpl->setVariable('COMPONENT', $default_renderer->render($triggered));
            $tpl->parseCurrentBlock();
        }
    }


	protected function renderClose($component) {
		$tpl = $this->getTemplate("tpl.close.html", true, true);
		// This is required as the rendering seems to only create any output at all
		// if any var was set or block was touched.
		$tpl->setVariable("FORCE_RENDERING", "");
		$this->maybeRenderId($component, $tpl);
		return $tpl->get();
	}

	protected function maybeRenderId($component, $tpl) {
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
