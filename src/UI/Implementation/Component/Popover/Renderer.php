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
		$tpl = $this->getTemplate('tpl.popover.html', true, true);
		$tpl->setVariable('FORCE_RENDERING', '');
		/** @var Component\Popover\Popover $component */
		$options = array(
			'title' => $this->escape($component->getTitle()),
			'placement' => $component->getPosition(),
			'multi' => true,
			'template' => str_replace('"', '\"', $tpl->get()),
		);
		// Check if the content is rendered async or via DOM
		$content_id = $this->createId();
		if ($component->getAsyncContentUrl()) {
			$options['type'] = 'async';
			$options['url'] = $component->getAsyncContentUrl();
		} else {
			$options['url'] = "#{$content_id}";
		}
		$options = json_encode($options);
		$show = $component->getShowSignal();
		$this->getJavascriptBinding()->addOnLoadCode("
			$(document).on('{$show}', function(event, signalData) { 
				il.UI.popover.show(signalData.triggerer.attr('id'), JSON.parse('{$options}'), signalData);
			});"
		);
		$replace = $component->getReplaceContentSignal('');
		$this->getJavascriptBinding()->addOnLoadCode("
			$(document).on('{$replace}', function(event, signalData) { 
				console.log(signalData);
				il.UI.popover.replaceContent('{$show}', signalData);
			});"
		);
		if (!$component->getAsyncContentUrl()) {
			$tpl = $this->getTemplate('tpl.popover-content.html', true, true);
			$tpl->setVariable('ID', $content_id);
			$tpl->setVariable('CONTENT', $default_renderer->render($component->getContent()));
			return $tpl->get();
		}
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/libs/node_modules/webui-popover/dist/jquery.webui-popover.min.js');
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
