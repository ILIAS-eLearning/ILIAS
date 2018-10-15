<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;
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
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		} elseif ($input instanceof Component\Input\Field\Numeric) {
			$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
		} else {
			throw new \LogicException("Cannot render '" . get_class($input) . "'");
		}

		return $this->renderProxyFieldWithContext($input_tpl, $input, $default_renderer);
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
	protected function renderProxyFieldWithContext(Template $input_tpl, Input $input, RendererInterface $default_renderer) {

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);

		$tpl->setCurrentBlock("addon_left");
		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("filter_field");
		$tpl->setVariable("FILTER_FIELD", $this->renderProxyField($input_tpl, $input, $default_renderer));
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
	protected function renderProxyField(Template $input_tpl, Input $input, RendererInterface $default_renderer) {

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.filter_field.html", true, true);

		$content = $this->renderInputFieldWithContext($input_tpl, $input);
		$popover = $f->popover()->standard($f->legacy($content))->withVerticalPosition();
		$tpl->setVariable("POPOVER", $default_renderer->render($popover));

		$prox = new ProxyFilterField();
		$prox = $prox->withOnClick($popover->getShowSignal());

		$this->maybeRenderId($prox, $tpl);
		return $tpl->get();
	}


	/**
	 * @param Template $input_tpl
	 * @param Input    $input
	 *
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Input $input) {
		return $this->renderInputField($input_tpl, $input);
	}


	/**
	 * @param Template $tpl
	 * @param Input    $input
	 * @param RendererInterface    $default_renderer
	 *
	 * @return string
	 */
	protected function renderInputField(Template $tpl, Input $input) {

		switch (true) {
			case ($input instanceof Text):
				$tpl->setVariable("NAME", $input->getName());

				if ($input->getValue() !== null) {
					$tpl->setCurrentBlock("value");
					$tpl->setVariable("VALUE", $input->getValue()."::");
					$tpl->parseCurrentBlock();
				}

				$input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
				$this->maybeRenderId($input, $tpl);
				break;
		}

		return $tpl->get();
	}


	/**
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderAddField(RendererInterface $default_renderer) {

		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);

		$list = $f->listing()->unordered([$f->button()->shy("Label 8", "#"), $f->button()->shy("Label 9", "#"), $f->button()->shy("Label 10", "#")]);
		$popover = $f->popover()->standard($list)->withVerticalPosition();
		$tpl->setVariable("POPOVER", $default_renderer->render($popover));
		$add = $f->button()->bulky($f->glyph()->add(), "", "#")->withOnClick($popover->getShowSignal());

		$tpl->setCurrentBlock("filter_field");
		$tpl->setVariable("FILTER_FIELD", $default_renderer->render($add));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}


	/**
	 * @param Component\JavascriptBindable $component
	 * @param Template                     $tpl
	 */
	protected function maybeRenderId(Component\JavascriptBindable $component, $tpl) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock("id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Input/Container/filter.js');
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return [
			Component\Input\Field\Text::class,
			Component\Input\Field\Numeric::class,
			Component\Input\Field\Group::class
		];
	}
}