<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;

/**
 * Base class for all component renderers.
 */
abstract class AbstractRenderer implements Renderer {
	/**
	 * @var	TemplateFactory
	 */
	protected $tpl_factory;

	/**
	 * Component renderers must only depend on a Template Factory.
	 */
	final public function __construct(TemplateFactory $tpl_factory) {
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
	 * @return	Template
	 */
	final protected function getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks) {
		$component = $this->getMyComponent();
		$path = "src/UI/templates/default/$component/$name";
		return $this->tpl_factory->getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
	}

	private function getMyComponent() {
		$class = get_class($this);
		$matches = array();
		// Extract component
		$re = "%ILIAS\\\\UI\\\\Implementation\\\\(\\w+)\\\\(\\w+)%";
		if (preg_match($re, $class, $matches) !== 1) {
			throw new \LogicException(
				"The Renderer needs to be located in ILIAS\\UI\\Implemenation\\*.");
		}
		return $matches[1];
	}
}
