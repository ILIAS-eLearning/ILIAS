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
		// Render the content of the popover into DOM with an ID
		$content_id = $this->createId();
		$show = $component->getShowSignal();
		$tpl = $this->getTemplate('tpl.popover.html', true, true);
		$tpl->setVariable('FORCE_RENDERING', '');
		$options = json_encode(array(
			'title' => $this->escape($component->getTitle()),
			'url' => "#{$content_id}",
			'placement' => $component->getPosition(),
			'multi' => true,
			'template' => str_replace('"', '\"', $tpl->get()),
		));
		$this->getJavascriptBinding()->addOnLoadCode("
			$(document).on('{$show}', function(event, data) { 
				var triggerer_id = data.triggerer.attr('id');
				var options = JSON.parse('{$options}');
				options.trigger = (data.type == 'mouseenter') ? 'hover' : data.type;
				il.UI.popover.show(triggerer_id, options);
			});"
		);
		$tpl = $this->getTemplate('tpl.popover-content.html', true, true);
		$tpl->setVariable('ID', $content_id);
		$tpl->setVariable('CONTENT', $default_renderer->render($component->getContent()));
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/libs/node_modules/webui-popover/dist/jquery.webui-popover.min.js');
		$registry->register('./src/UI/templates/libs/node_modules/webui-popover/dist/jquery.webui-popover.min.css');
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
