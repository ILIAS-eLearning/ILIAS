<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var $component Input
		 */
		$this->checkComponent($component);

		if ($component instanceof Component\Input\Field\Group) {
			/**
			 * @var $component Group
			 */
			return $this->renderFieldGroups($component, $default_renderer);
		}

		return $this->renderNoneGroupInput($component, $default_renderer);
	}


	/**
	 * @param Component\Input\Field\Input $input
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderNoneGroupInput(Component\Input\Field\Input $input, RendererInterface $default_renderer) {
		$input_tpl = null;

		if ($input instanceof Component\Input\Field\Text) {
			$input_tpl = $this->getTemplate("tpl.text_filter.html", true, true);
		} else {
			throw new \LogicException("Cannot render '" . get_class($input) . "'");
		}

		return $this->renderInputFieldWithContext($input_tpl, $input, $default_renderer);
	}


	/**
	 * @param Group             $group
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderFieldGroups(Group $group, RendererInterface $default_renderer) {

		$inputs = "";
		foreach ($group->getInputs() as $input) {
			$inputs .= $default_renderer->render($input);
		}

		$inputs .= $this->renderAddField($default_renderer);

		return $inputs;
	}


	/**
	 * @param Template $input_tpl
	 * @param Input    $input
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Input $input, RendererInterface $default_renderer) {

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);

		$tpl->setCurrentBlock("addon_left");
		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("input");
		$tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input, $default_renderer));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("addon_right");
		$tpl->setVariable("DELETE", $default_renderer->render($f->glyph()->remove()));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}


	/**
	 * @param Template $tpl
	 * @param Input    $input
	 * @param RendererInterface    $default_renderer
	 *
	 * @return string
	 */
	protected function renderInputField(Template $tpl, Input $input, RendererInterface $default_renderer) {

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.text_filter.html", true, true);

		//$input1 = $f->input()->field()->text("Text 1", "Das ist Text1");
		$list1 = $f->listing()->unordered([$f->button()->shy("Label 8", "#"), $f->button()->shy("Label 9", "#"), $f->button()->shy("Label 10", "#")]);
		$popover = $f->popover()->standard($list1)->withVerticalPosition();
		$tpl->setVariable("POPOVER", $default_renderer->render($popover));
		$input = $input->withOnClick($popover->getShowSignal());

		$tpl->setVariable("NAME", $input->getName());

		if ($input->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $input->getValue());
			$tpl->parseCurrentBlock();
		}

		$this->maybeRenderId($input, $tpl);
		return $tpl->get();
	}


	/**
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderAddField(RendererInterface $default_renderer) {

		/*
		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);
		$input = $f->input()->field()->text("Add");
		$input_tpl = $this->getTemplate("tpl.text_filter.html", true, true);

		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input));
		$tpl->setVariable("DELETE", $default_renderer->render($f->glyph()->add()));
		*/

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);
		$input1 = $f->input()->field()->text("Text 1", "Das ist Text1")->withValue("Value 1");
		//$input2 = $f->input()->field()->numeric("Text 2", "Das ist Text2");
		//$form = $f->input()->container()->form()->standard("#", [$input1, $input2]);
		//$section = $f->input()->field()->section([$input1, $input2], "AAA");
		//$list = $f->listing()->unordered([$f->button()->shy("Label 8", "#"), $f->button()->shy("Label 9", "#"), $f->button()->shy("Label 10", "#")]);
		$popover = $f->popover()->standard($input1)->withVerticalPosition();
		$tpl->setVariable("POPOVER", $default_renderer->render($popover));
		$add = $f->button()->bulky($f->glyph()->add(), "", "#")->withOnClick($popover->getShowSignal());

		$tpl->setCurrentBlock("input");
		$tpl->setVariable("INPUT", $default_renderer->render($add));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}


	protected function maybeRenderId(Component\Component $component, $tpl) {
		$id = $this->bindJavaScript($component);
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
		return [
			Component\Input\Field\Text::class,
			Component\Input\Field\Group::class
		];
	}
}