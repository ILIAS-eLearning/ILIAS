<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Test;

use \ILIAS\UI\Implementation\Render\ResourceRegistry;
use \ILIAS\UI\Implementation\Component\ComponentHelper;
use \ILIAS\UI\Renderer as DefaultRenderer;
use \ILIAS\UI\Component\Component;

class TestComponent implements \ILIAS\UI\Component\Component {
	use ComponentHelper;

	public function __construct($text) {
		$this->text = $text;
	}
}

class Renderer implements \ILIAS\UI\Implementation\Render\ComponentRenderer {
	public function render(Component $component, DefaultRenderer $default_renderer) {
		return $component->text;
	}

	public function registerResources(ResourceRegistry $registry) {
		$registry->register("test.js");
	}
}
