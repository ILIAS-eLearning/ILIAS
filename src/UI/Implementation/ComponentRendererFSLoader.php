<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Factory as RootFactory;
use ILIAS\UI\Component\Component;

/**
 * Loads renderers for components from the file system.
 */
class ComponentRendererFSLoader implements ComponentRendererLoader {
	/**
	 * @var	RootFactory
	 */
	private $ui_factory;

	/**
	 * @var	Render\TemplateFactory
	 */
	private $tpl_factory;

	/**
	 * @var	\ilLanguage
	 */
	private $lng;

	/**
	 * @var	Render\JavaScriptBinding
	 */
	private $js_binding;

	public function __construct(RootFactory $ui_factory, Render\TemplateFactory $tpl_factory, \ilLanguage $lng, Render\JavaScriptBinding $js_binding) {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFor(Component $component, array $contexts) {
		$class = get_class($component);
		$context_names = $this->getContextNames($contexts);
		$renderer_classes = $this->getRendererNamesFor($class, $context_names);
		foreach ($renderer_classes as $renderer_class) {
			if (class_exists($renderer_class)) {
				return new $renderer_class
					($this->ui_factory
					, $this->tpl_factory
					, $this->lng
					, $this->js_binding
					);
			}
		}
		throw new \LogicException("No rendered for '".$class."' found.");
    }

	/**
	 * Get the possible class names for the renderer of Component class under the given
     * contexts.
	 *
	 * @param	string		$class
	 * @param	string[]	$contexts
	 * @return	string
	 */
	protected function getRendererNamesFor($class, array $contexts) {
		$parts = explode("\\", $class);
		$parts[count($parts)-1] = "Renderer";
		$base = implode("\\", $parts);
		if (count($contexts) == 0) {
			return [$base];
		}
		$ret = [$base."_".implode("_", $contexts)];
		$last = array_pop($contexts);
		while($last) {
			$ret[] = $base."_".$last;
			$last = array_pop($contexts);
		}
		$ret[] = $base;
		return $ret;
	}

	/**
	 * Get and collapse the names of the passes components.
	 *
	 * @param	Component[]	$contexts
	 * @return	string[]
	 */
	protected function getContextNames(array $contexts) {
		$names = [];
		foreach ($contexts as $context) {
			$names[] = str_replace(" ", "", $context->getName());
		}
		return $names;
	}
}
