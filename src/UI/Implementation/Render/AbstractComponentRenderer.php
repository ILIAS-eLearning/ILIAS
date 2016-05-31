<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Template;

/**
 * Base class for all component renderers.
 */
abstract class AbstractComponentRenderer implements ComponentRenderer {
	/**
	 * @var	TemplateFactory
	 */
	protected $tpl_factory;

	/**
	 * Component renderers must only depend on a Template Factory.
	 */
	final public function __construct(Template\TemplateFactory $tpl_factory) {
		$this->tpl_factory = $tpl_factory;
	}

	/**
	 * Get template of component this renderer is made for.
	 *
	 * Serves as a wrapper around instantiation of ilTemplate, exposes
	 * a smaller interface.
	 *
	 * @param	string	$name
	 * @param	bool	$purge_unfilled_vars
	 * @param	bool	$purge_unused_blocks
	 * @throws	\InvalidArgumentException	if there is no such template
	 * @return	Template\Template
	 */
	final protected function getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks) {
		$component = $this->getMyComponent();
		$path = "src/UI/templates/default/$component/$name";
		return $this->tpl_factory->getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
	}

	/**
	 * Check if a given component fits this renderer and throw \LogicError if that is not
     * the case.
	 *
	 * @param	Component			$component
	 * @throws	\LogicException		if component does not fit.
     * @return  null
	 */
	final protected function checkComponent(Component $component) {
		$interface = $this->getComponentInterfaceName();
		if(!($component instanceof $interface)) {
			throw new \LogicException(
				"Expected $cmp, found '".get_class($component)."' when rendering.");
		}
	}

	/**
	 * Get the name of the component-interface this renderer is supposed to render.
	 *
	 * ATTENTION: Fully qualified please!
	 *
	 * @return string
	 */
	abstract protected function getComponentInterfaceName();

	// TODO: We might want to cache this.
	private function getMyComponent() {
		$class = get_class($this);
		$matches = array();
		// Extract component
		$re = "%ILIAS\\\\UI\\\\Implementation\\\\Component\\\\(\\w+)\\\\(\\w+)%";
		if (preg_match($re, $class, $matches) !== 1) {
			throw new \LogicException(
				"The Renderer needs to be located in ILIAS\\UI\\Implementation\\Component\\*.");
		}
		return $matches[1];
	}
}
