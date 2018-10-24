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
class FilterContextRenderer extends Renderer {

	/**
	 * @param Template $input_tpl
	 * @param Input    $input
	 * @param null     $id
	 * @param null     $dependant_group_html
	 *
	 * @return string
	 */
	protected function renderInputFieldWithContext(Template $input_tpl, Input $input, $id = null, $dependant_group_html = null) {

		$tpl = $this->getTemplate("tpl.context_filter.html", true, true);
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
}
