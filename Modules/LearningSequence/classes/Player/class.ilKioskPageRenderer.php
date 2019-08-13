<?php

declare(strict_types=1);

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Listing\Workflow\Workflow;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;



/**
 * Class ilKioskPageRenderer
 */
class ilKioskPageRenderer
{
	public function __construct(
		ilGlobalPageTemplate $il_global_template,
		MetaContent $layout_meta_content,
		Renderer $ui_renderer,
		ilTemplate $kiosk_template,
		ilLSTOCGUI $toc_gui,
		ilLSLocatorGUI $loc_gui,
		string $window_base_title
	) {
		$this->il_tpl = $il_global_template;
		$this->layout_meta_content = $layout_meta_content;
		$this->ui_renderer = $ui_renderer;
		$this->tpl = $kiosk_template;
		$this->toc_gui = $toc_gui;
		$this->loc_gui = $loc_gui;
		$this->window_base_title = $window_base_title;
	}

	public function render(
		string $lso_title,
		LSControlBuilder $control_builder,
		string $obj_title,
		Component $icon,
		array $content,
		Workflow $curriculum
	): string {

		$this->tpl->setVariable("HTML_PAGE_TITLE",
			$this->window_base_title .' - ' .$lso_title
		);

		$this->tpl->setVariable("TOPBAR_CONTROLS",
			$this->ui_renderer->render($control_builder->getExitControl())
		);

		$this->tpl->setVariable("TOPBAR_TITLE", $lso_title);

		$this->tpl->setVariable("OBJECT_ICON",
			$this->ui_renderer->render($icon)
		);
		$this->tpl->setVariable("OBJECT_TITLE", $obj_title);

		$this->tpl->setVariable("PLAYER_NAVIGATION",
			$this->ui_renderer->render([
				$control_builder->getPreviousControl(),
				$control_builder->getNextControl()
			])
		);

		$controls = $control_builder->getControls();

		//ensure done control is first element
		if($control_builder->getDoneControl()) {
			array_unshift($controls, $control_builder->getDoneControl());
		}
		//also shift start control up front - this is for legacy-views only!
		if($control_builder->getStartControl()) {
			array_unshift($controls, $control_builder->getStartControl());
		}


		//TODO: insert toggles

		$this->tpl->setVariable("OBJ_NAVIGATION",
			$this->ui_renderer->render($controls)
		);


		$this->tpl->setVariable("VIEW_MODES",
			$this->ui_renderer->render($control_builder->getModeControls())
		);

		if($control_builder->getLocator()) {
			$this->tpl->setVariable('LOCATOR',
				$this->ui_renderer->render(
					$this->loc_gui
						->withItems($control_builder->getLocator()->getItems())
						->getComponent()
				)
			);
		}

		$this->tpl->setVariable('CONTENT',
			$this->ui_renderer->render($content)
		);
		$this->tpl->setVariable('CURRICULUM',
			$this->ui_renderer->render($curriculum)
		);

		if($control_builder->getToc()) {
			$this->tpl->touchBlock("sidebar_space");
			$this->tpl->setVariable("SIDEBAR",
				$this->toc_gui
					->withStructure($control_builder->getToc()->toJSON())
					->getHTML()
			);
		} else {
			$this->tpl->touchBlock("sidebar_disabled");
		}

		$tpl = $this->setHeaderVars($this->tpl);
		return $tpl->get();
	}

	protected function setHeaderVars(ilTemplate $tpl)
	{
		$js_files = $this->getJsFiles();
		$js_olc = $this->getOnLoadCode();
		$css_files = $this->getCSSFiles();
		$css_inline = $this->getInlineCSS();

		foreach ($js_files as $js_file) {
			$tpl->setCurrentBlock("js_file");
			$tpl->setVariable("JS_FILE", $js_file);
			$tpl->parseCurrentBlock();
		}

		foreach ($css_files as $css_file) {
			$tpl->setCurrentBlock("css_file");
			$tpl->setVariable("CSS_FILE", $css_file);
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("CSS_INLINE", $css_inline);
		$tpl->setVariable("OLCODE", $js_olc);

		return $tpl;
	}

	protected function getJsFiles(): array
	{
		$js_files = [
			'./Services/JavaScript/js/Basic.js'
		];
		foreach ($this->layout_meta_content->getJs()->getItemsInOrderOfDelivery() as $js_file) {
			$file = $js_file->getContent();
			if(!strpos($file, 'Services/FileUpload/js')) {
				$js_files[] = $file;
			}
		}
		return $js_files;
	}

	protected function getOnLoadCode(): string
	{
		$js_inline = [];
		foreach ($this->layout_meta_content->getOnloadCode()->getItemsInOrderOfDelivery() as $on_load_code) {
			$js_inline[] = $on_load_code->getContent();
		}
		return implode(PHP_EOL, $js_inline);
	}

	protected function getCSSFiles(): array
	{
		$css_files = $this->layout_meta_content->getCSS()->getItemsInOrderOfDelivery();
		$css_files = array_map(
			function($css_file) {
				return $css_file->getContent();
			}
			,$css_files
		);
		$css_files[] = \ilUtil::getStyleSheetLocation("filesystem", "delos.css");
		$css_files[] = \ilUtil::getStyleSheetLocation();
		$css_files[] = \ilUtil::getNewContentStyleSheetLocation();
		return $css_files;
	}

	protected function getInlineCSS(): string
	{
		$css_inline = [];
		foreach ($this->layout_meta_content->getInlineCss()->getItemsInOrderOfDelivery() as $inline_css) {
			$css_inline[] = $inline_css->getContent();
		}
		return implode(PHP_EOL, $css_inline);
	}

}
