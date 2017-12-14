<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
class Renderer extends AbstractComponentRenderer {

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

		return $this->renderNoneGroupInput($component);
	}


	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Input/Field/dependantGroup.js');
	}


	/**
	 * @param Component\Input\Field\Input $input
	 *
	 * @return string
	 */
	protected function renderNoneGroupInput(Component\Input\Field\Input $input) {
		$input_tpl = null;

		if ($input instanceof Component\Input\Field\Text) {
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		} else {
			if ($input instanceof Component\Input\Field\Numeric) {
				$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
			} else {
				throw new \LogicException("Cannot render '" . get_class($input) . "'");
			}
		}

		return $this->renderInputFieldWithContext($input_tpl, $input);
	}


	/**
	 * @param Group             $group
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderFieldGroups(Group $group, RendererInterface $default_renderer) {
		if ($group instanceof Component\Input\Field\DependantGroup) {
			/**
			 * @var $group DependantGroup
			 */
			return $this->renderDependantGroup($group, $default_renderer);
		} else {
			if ($group instanceof Component\Input\Field\Checkbox) {
				/**
				 * @var $group Checkbox
				 */
				$input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
				$dependant_group_html = "";
				$id = "";
				if ($group->getDependantGroup()) {
					$dependant_group_html = $default_renderer->render($group->getDependantGroup());
					$id = $this->bindJavaScript($group);
				}

				$html = $this->renderInputFieldWithContext($input_tpl, $group, $id, $dependant_group_html);

				return $html;
			} else {
				if ($group instanceof Component\Input\Field\Section) {
					/**
					 * @var $group Section
					 */
					return $this->renderSection($group, $default_renderer);
				}
			}
		}
		$inputs = "";
		foreach ($group->getInputs() as $input) {
			$inputs .= $default_renderer->render($input);
		}

		return $inputs;
	}


	/**
	 * @param Component\JavascriptBindable $component
	 * @param                              $tpl
	 */
	protected function maybeRenderId(Component\JavascriptBindable $component, Template $tpl) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param Section           $section
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderSection(Section $section, RendererInterface $default_renderer) {
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

		foreach ($section->getInputs() as $input) {
			$inputs_html .= $default_renderer->render($input);
		}
		$section_tpl->setVariable("INPUTS", $inputs_html);

		return $section_tpl->get();
	}


	/**
	 * @param DependantGroup    $dependant_group
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderDependantGroup(DependantGroup $dependant_group, RendererInterface $default_renderer) {
		$dependant_group_tpl = $this->getTemplate("tpl.dependant_group.html", true, true);

		$toggle = $dependant_group->getToggleSignal();
		$show = $dependant_group->getShowSignal();
		$hide = $dependant_group->getHideSignal();
		$init = $dependant_group->getInitSignal();

		$dependant_group = $dependant_group->withAdditionalOnLoadCode(function ($id) use ($toggle, $show, $hide, $init) {
			return "il.UI.Input.dependantGroup.init('$id',{toggle:'$toggle',show:'$show',hide:'$hide',init:'$init'});";
		});

		/**
		 * @var $dependant_group DependantGroup
		 */
		$id = $this->bindJavaScript($dependant_group);
		$dependant_group_tpl->setVariable("ID", $id);

		$inputs_html = "";

		foreach ($dependant_group->getInputs() as $input) {
			$inputs_html .= $default_renderer->render($input);
		}
		$dependant_group_tpl->setVariable("CONTENT", $inputs_html);

		return $dependant_group_tpl->get();
	}


	/**
	 * @param Template $input_tpl
	 * @param Input    $input
	 * @param null     $id
	 * @param null     $dependant_group_html
	 *
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Input $input, $id = null, $dependant_group_html = null) {
		$tpl = $this->getTemplate("tpl.context_form.html", true, true);
		/**
		 * TODO: should we through an error in case for no name or render without name?
		 *
		 * if(!$input->getName()){
		 * throw new \LogicException("Cannot render '".get_class($input)."' no input name given.
		 * Is there a name source attached (is this input packed into a container attaching
		 * a name source)?");
		 * } */
		if ($input->getName()) {
			$tpl->setVariable("NAME", $input->getName());
		} else {
			$tpl->setVariable("NAME", "");
		}

		$tpl->setVariable("LABEL", $input->getLabel());
		$tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input, $id));

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

		if ($dependant_group_html !== null) {
			$tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
		}

		return $tpl->get();
	}


	/**
	 * @param Template $tpl
	 * @param Input    $input
	 * @param          $id
	 *
	 * @return string
	 */
	protected function renderInputField(Template $tpl, Input $input, $id) {
		$tpl->setVariable("NAME", $input->getName());

		if ($input->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $input->getValue());
			$tpl->parseCurrentBlock();
		}
		if ($id) {
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
		return [
			Component\Input\Field\Text::class,
			Component\Input\Field\Numeric::class,
			Component\Input\Field\Group::class,
			Component\Input\Field\Section::class,
			Component\Input\Field\Checkbox::class,
			Component\Input\Field\DependantGroup::class,
		];
	}
}
