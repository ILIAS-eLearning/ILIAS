<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Input\Field\Group) {
			$inputs = "";
			foreach($component->getInputs() as $input) {
				$inputs .= $default_renderer->render($input);
			}
			return $inputs;
		}

		$input_tpl = null;
		$sub_section = "";

		if ($component instanceof Component\Input\Field\Text) {
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		}else if($component instanceof Component\Input\Field\Numeric){
			$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
		} else if($component instanceof Component\Input\Field\Checkbox){
			$input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
			if($component->getSubSection()){
				$sub_section = $default_renderer->render($component->getSubSection());
				$sub_section = "<div class='subish'>".$sub_section."</div>";

				$component = $component->withOnLoadCode(function($id){
					return "$( 'input' ).on('click', function() {console.log('checkbox_clicked');});";
				});
				$id = $this->bindJavaScript($component);
				//$tpl->setVariable('ID', $id);
			}
		} else{
			throw new \LogicException("Cannot render '".get_class($component)."'");
		}

		//TODO: How to solve this, Inputs will have a different frame depending on the
		// context...
		return $this->renderContext(
				$this->renderInput($input_tpl,$component, $default_renderer),
				$component,
				$default_renderer).$sub_section;
	}

	/**
	 * @param $input_html
	 * @param Component\Input\Field\Input $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function renderContext($input_html, Component\Input\Field\Input $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.context-form.html", true, true);
		$tpl->setVariable("NAME", $component->getName());
		$tpl->setVariable("LABEL", $component->getLabel());
		$tpl->setVariable("INPUT", $input_html);

		if ($component->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $component->getByline());
			$tpl->parseCurrentBlock();
		}

		if ($component->isRequired()) {
			$tpl->touchBlock("required");
		}

		if ($component->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $component->getError());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param Template $tpl
	 * @param Component\Input\Field\Input $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function renderInput(Template $tpl, Component\Input\Field\Input $component, RendererInterface $default_renderer) {
		$tpl->setVariable("NAME", $component->getName());

		if ($component->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $component->getValue());
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return [Component\Input\Field\Text::class,Component\Input\Field\Numeric::class,
				Component\Input\Field\Group::class,Component\Input\Field\Section::class,
				Component\Input\Field\Checkbox::class];
	}
}
