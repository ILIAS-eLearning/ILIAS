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

		if($component instanceof Component\Input\Field\Group){
			return $this->renderFieldGroups($component, $default_renderer);
		}
		return $this->renderNoneFieldGroupInput($component, $default_renderer);
	}

	protected function renderNoneFieldGroupInput(Component\Input\Field\Input $input, RendererInterface $default_renderer){
		$input_tpl = null;

		if ($input instanceof Component\Input\Field\Text) {
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		}else if($input instanceof Component\Input\Field\Numeric){
			$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
		} else{
			throw new \LogicException("Cannot render '".get_class($input)."'");
		}

		//TODO: How to solve this, Inputs will have a different frame depending on the
		//context...
		return $this->renderInputFieldWithContext($input_tpl,$input, $default_renderer);
	}

	protected function renderFieldGroups(Component\Input\Field\Group $group, RendererInterface $default_renderer){
		if ($group instanceof Component\Input\Field\SubSection) {
			return $this->renderSubSection($group, $default_renderer);
		}else if($group instanceof Component\Input\Field\Checkbox){
			$input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
			$sub_section = "";
			if($group->getSubSection()){
				$sub_section = $default_renderer->render($group->getSubSection());
				$id = $this->bindJavaScript($group);
			}
			$html = $this->renderInputFieldWithContext($input_tpl,$group, $default_renderer,$id);
			return $html.$sub_section;
		} else if($group instanceof Component\Input\Field\Section){
			return $this->renderSection($group, $default_renderer);
		}
		$inputs = "";
		foreach($group->getInputs() as $input) {
			$inputs .= $default_renderer->render($input);
		}
		return $inputs;
	}

	protected function maybeRenderId(Component\Component $component, $tpl) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}


	protected function renderSection(Component\Input\Field\Section $section, RendererInterface $default_renderer){
		$section_tpl = $this->getTemplate("tpl.section.html", true, true);
		$section_tpl->setVariable("LABEL", $section->getLabel());

		if ($section->getByline() !== null) {
			$section_tpl->setCurrentBlock("byline");
			$section_tpl->setVariable("BYLINE", $section->getByline());
			$section_tpl->parseCurrentBlock();
		}

		if ($section->getError() !== null) {
			$section_tpl->setCurrentBlock("error");
			$section_tpl->setVariable("ERROR", $section->getError());
			$section_tpl->parseCurrentBlock();
		}
		$inputs_html = "";

		foreach($section->getInputs() as $input) {
			$inputs_html .= $default_renderer->render($input);
		}
		$section_tpl->setVariable("INPUTS", $inputs_html);


		return $section_tpl->get();
	}

	protected function renderSubSection(Component\Input\Field\SubSection $sub_section, RendererInterface $default_renderer){
		$sub_section_tpl = $this->getTemplate("tpl.sub_section.html", true, true);

		$toggle = $sub_section->getToggleSignal();

		$sub_section =  $sub_section->withAdditionalOnLoadCode(function($id) use ($toggle) {
			return "$(document).on('{$toggle}', function(signal,params) { console.log(signal,params); $($id).toggle();});";
		});

		$id = $this->bindJavaScript($sub_section);
		$sub_section_tpl->setVariable("ID", $id);

		$inputs_html = "";

		foreach($sub_section->getInputs() as $input) {
			$inputs_html .= $default_renderer->render($input);
		}
		$sub_section_tpl->setVariable("CONTENT", $inputs_html);
		return $sub_section_tpl->get();
	}

	/**
	 * @param $input_html
	 * @param Component\Input\Field\Input $input
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Component\Input\Field\Input $input, RendererInterface $default_renderer,$id = null) {
		$tpl = $this->getTemplate("tpl.context-form.html", true, true);
		$tpl->setVariable("NAME", $input->getName());
		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input,$id));

		if ($input->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $input->getByline());
			$tpl->parseCurrentBlock();
		}

		if ($input->isRequired()) {
			$tpl->touchBlock("required");
		}

		if ($input->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $input->getError());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param Template $tpl
	 * @param Component\Input\Field\Input $input
	 * @param RendererInterface $default_renderer
	 * @param $id
	 * @return string
	 */
	protected function renderInputField(Template $tpl, Component\Input\Field\Input $input, $id) {
		$tpl->setVariable("NAME", $input->getName());

		if ($input->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $input->getValue());
			$tpl->parseCurrentBlock();
		}
		if($id){
			$tpl->setCurrentBlock("id");
			$tpl->setVariable("ID", $id);
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
