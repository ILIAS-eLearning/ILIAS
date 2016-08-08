<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Base class for all component renderers.
 *
 * Offers some convenience methods for renderes, users only needs to implement
 * ComponentRenderer::render. Assumes that there is no special resource the
 * component requires.
 */
abstract class AbstractComponentRenderer implements ComponentRenderer {
	/**
	 * @var	Factory
	 */
	private $ui_factory;

	/**
	 * @var	TemplateFactory
	 */
	private $tpl_factory;

	/**
	 * @var	\ilLanguage
	 */
	private $lng;

	/**
	 * Component renderers must only depend on a UI-Factory and a Template Factory.
	 */
	final public function __construct(Factory $ui_factory, TemplateFactory $tpl_factory, \ilLanguage $lng) {
		$this->ui_factory = $ui_factory;
		$this->tpl_factory = $tpl_factory;
		$this->lng = $lng;
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
	}

	/**
	 * Get a UI factory.
	 *
	 * This could be used to create and render subcomponents like close buttons, etc.
	 *
	 * @return	Factory
	 */
	final protected function getUIFactory() {
		return $this->ui_factory;
	}

	/**
	 * Get a text from the language file.
	 *
	 * @param	string	$id
	 * @return	string
	 */
	final public function txt($id) {
		return $this->lng->txt($id);
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
	 * @return	\ILIAS\UI\Implementation\Render\Template
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
		$interfaces = $this->getComponentInterfaceName();
		if(!is_array($interfaces)){
			throw new \LogicException(
					"Expected array, found '".(string)(null)."' when rendering.");
		}

		foreach ($interfaces as $interface) {
			if ($component instanceof $interface) {
				return;
			}
		}
		$ifs = implode(", ", $interfaces);
		throw new \LogicException(
			"Expected $ifs, found '".get_class($component)."' when rendering.");
	}

	/**
	 * Get the name of the component-interface this renderer is supposed to render.
	 *
	 * ATTENTION: Fully qualified please!
	 *
	 * @return string[]
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
