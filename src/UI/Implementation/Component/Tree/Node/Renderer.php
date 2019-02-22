<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Tree\Node;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		$tpl_name = "tpl.node.html";
		$tpl = $this->getTemplate($tpl_name, true, true);

		$tpl->setVariable("LABEL", $component->getLabel());


		$subnodes_html = $default_renderer->render($component->getSubnodes());
		$tpl->setVariable("SUBNODES", $subnodes_html);


		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Node\Simple::class
		);
	}
}