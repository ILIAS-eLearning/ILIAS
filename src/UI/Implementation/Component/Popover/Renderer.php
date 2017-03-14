<?php
namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);
		/** @var Component\Popover\Popover $component */
		$show = $component->getShowSignal();
		$js = $this->getJavascriptBinding();
		$tpl = $this->getTemplate('tpl.popover.html', true, true);
		$tpl->setVariable('FORCE_RENDERING', '');
		$options = json_encode(array(
			'container' => 'body',
			'title' => $this->escape($component->getTitle()),
			'content' => $this->escape($component->getText()),
			'placement' => $component->getPosition(),
			'trigger' => 'manual',
			'html' => true,
			'template' => str_replace('"', '\"', $tpl->get()),
		));
		$js->addOnLoadCode("
		$(document).on('{$show}', function() { 
			var \$triggerer = $('#' + event.target.id);
			il.UI.popover.toggle(\$triggerer, '{$options}');
		});"
		);
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Popover/popover.js');
	}

	/**
	 * @param string $str
	 * @return string
	 */
	protected function escape($str) {
		return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(Component\Popover\Popover::class);
	}
}
