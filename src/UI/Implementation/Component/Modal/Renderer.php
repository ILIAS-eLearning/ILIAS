<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Modal\Interruptive) {
			return $this->renderInterruptive($component, $default_renderer);
		} else if ($component instanceof Component\Modal\RoundTrip) {
			return $this->renderRoundTrip($component, $default_renderer);
		} else if ($component instanceof Component\Modal\Lightbox) {
			return $this->renderLightbox($component, $default_renderer);
		}
	}


	/**
	 * @param Component\Modal\Interruptive $modal
	 * @param RendererInterface            $default_renderer
	 *
	 * @return string
	 */
	protected function renderInterruptive(Component\Modal\Interruptive $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.interruptive.html', true, true);
		$tpl->setVariable('TITLE', $modal->getTitle());
		$tpl->setVariable('CONTENT', $default_renderer->render($modal->getContent()));
		$this->maybeRenderId($modal, $tpl);
		foreach ($modal->getButtons() as $button) {
			$connection = $this->getUIFactory()->connector()->onClick($button, $modal->getCloseAction());
			$tpl->setCurrentBlock('buttons');
			$tpl->setVariable('BUTTON', $default_renderer->render($button, $connection));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param Component\Modal\RoundTrip $modal
	 * @param RendererInterface         $default_renderer
	 *
	 * @return string
	 */
	protected function renderRoundTrip(Component\Modal\RoundTrip $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.roundtrip.html', true, true);
		$tpl->setVariable('TITLE', $modal->getTitle());
		$tpl->setVariable('CONTENT', $default_renderer->render($modal->getContent()));
		$tpl->setVariable('ID', $this->createId($modal));
		foreach ($modal->getActionButtons() as $button) {
			$tpl->setCurrentBlock('buttons');
			$tpl->setVariable('BUTTON', $default_renderer->render($button));
			$tpl->parseCurrentBlock();
		}
		// Cancel Button is always rendered after action buttons at the very end of the footer
		$cancel_button = $this->getCancelButton($modal->getCancelButtonLabel());
		$connection = $this->getUIFactory()->connector()->onClick($cancel_button, $modal->getCloseAction());
		$tpl->setVariable('CANCEL_BUTTON', $default_renderer->render($cancel_button, $connection));
		return $tpl->get();
	}


	/**
	 * @param Component\Modal\Lightbox $modal
	 * @param RendererInterface        $default_renderer
	 *
	 * @return string
	 */
	protected function renderLightbox(Component\Modal\Lightbox $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.lightbox.html', true, true);
		$tpl->setVariable('TITLE', $modal->getTitle());
		$tpl->setVariable('CONTENT', $default_renderer->render($modal->getContent()));
		$this->maybeRenderId($modal, $tpl);

		return $tpl->get();
	}


	protected function maybeRenderId($modal, $tpl) {
		$id = $this->createId($modal);
		if ($id !== NULL) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * Get a cancel button from the UI factory with the desired label by the modal
	 *
	 * @param string $txt_key
	 *
	 * @return Component\Button\Standard
	 */
	protected function getCancelButton($txt_key) {
		return $this->getUIFactory()->button()->standard($this->txt($txt_key), '');
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Modal\Interruptive::class,
			Component\Modal\RoundTrip::class,
			Component\Modal\Lightbox::class,
		);
	}
}
