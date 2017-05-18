<?php
namespace ILIAS\UI\Implementation\Component\Progressbar;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Progressbar\Progressbar;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;

class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array( Progressbar::class );
	}


	/**
	 * @inheritdocs
	 */
	public function render(Component $component, \ILIAS\UI\Renderer $default_renderer) {
		$this->checkComponent($component);
		$tpl = $this->getTemplate("tpl.progressbar.html", true, true);
		$tpl->setVariable("PERCENTAGE", $component->getPercentage());
		if ($component->getActive()) {
			$tpl->setVariable("ACTIVE", "active");
		}

		return $tpl->get();
	}
}