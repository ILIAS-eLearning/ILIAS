<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {

		$this->checkComponent($component);

		if ($component instanceof Component\Table\Presentation) {
				return $this->renderPresentationTable($component, $default_renderer);
		}

		if ($component instanceof Component\Table\PresentationRow) {
				return $this->renderPresentationRow($component, $default_renderer);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Table\PresentationRow::class,
			Component\Table\Presentation::class
		);
	}

	/**
	 * @param Component\Table\Presentation $component
	 * @param RendererInterface $default_renderer
	 * @return mixed
	 */
	protected function renderPresentationTable(Component\Table\Presentation $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.presentationtable.html", true, true);

		$tpl->setVariable("TITLE", $component->getTitle());

		$vcs = $component->getViewControls();
		if($vcs) {
			$tpl->touchBlock("viewcontrols");
			foreach ($vcs as $vc) {
				$tpl->setCurrentBlock("vc");
				$tpl->setVariable("VC", $default_renderer->render($vc));
				$tpl->parseCurrentBlock();
			}
		}

		foreach ($component->getRows() as $row) {
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW", $default_renderer->render($row));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param Component\Table\Presentation $component
	 * @param RendererInterface $default_renderer
	 * @return mixed
	 */
	protected function renderPresentationRow(Component\Table\PresentationRow $component, RendererInterface $default_renderer) {
		$f = $this->getUIFactory();
		$tpl = $this->getTemplate("tpl.presentationrow.html", true, true);
		$data = $component->getData();

		$component = $this->registerSignals($component->withResetSignals());
		$sig_show = $component->getShowSignal();
		$sig_hide = $component->getCloseSignal();
		$id = $this->bindJavaScript($component);

		$expander = $f->glyph()->expand("#")
			->withOnClick($sig_show);
		$collapser = $f->glyph()->collapse("#")
			->withOnClick($sig_hide);
		$shy_expander = $f->button()->shy("Details","#")
			->withOnClick($sig_show);


		$tpl->setVariable("ID",$id);
		$tpl->setVariable("EXPANDER", $default_renderer->render($expander));
		$tpl->setVariable("COLLAPSER", $default_renderer->render($collapser));
		$tpl->setVariable("SHY_EXPANDER", $default_renderer->render($shy_expander));


		$tpl->setVariable("TITLE", $data[$component->getTitleField()]);
		$tpl->setVariable("SUBTITLE", $data[$component->getSubtitleField()]);

		foreach ($component->getImportantFields() as $field => $label) {
			$tpl->setCurrentBlock("important_field");
			$tpl->setVariable("IMPORTANT_FIELD_LABEL", $label);
			$tpl->setVariable("IMPORTANT_FIELD_VALUE", $data[$field]);
			$tpl->parseCurrentBlock();
		}

		$description = array();
		foreach ($component->getDescriptionFields() as $field => $label) {
			$description[$label] = $data[$field];
		}
		$desclist = $f->listing()->descriptive($description);
		$tpl->setVariable("DESCLIST", $default_renderer->render($desclist));

		$further_fields_headline = $component->getFurtherFieldsHeadline();
		if($further_fields_headline) {
			$tpl->setVariable("FURTHER_FIELDS_HEADLINE", $further_fields_headline);
		}

		foreach ($component->getFurtherFields() as $field => $label) {
			$tpl->setCurrentBlock("further_field");
			$tpl->setVariable("FIELD_LABEL", $label);
			$tpl->setVariable("FIELD_VALUE", $data[$field]);
			$tpl->parseCurrentBlock();
		}

		foreach ($component->getButtons() as $button) {
			$tpl->setCurrentBlock("button");
			$tpl->setVariable("BUTTON", $default_renderer->render($button));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Table/presentation.js');
	}

	/**
	 * @param Component\Modal\Modal $modal
	 * @param string $id
	 */
	protected function registerSignals(Component\Table\PresentationRow $component) {
		$show = $component->getShowSignal();
		$close = $component->getCloseSignal();
		return $component->withAdditionalOnLoadCode(function($id) use ($show, $close) {
			return
				"$(document).on('{$show}', function() { il.UI.table.presentation.expandRow('{$id}'); return false; });".
				"$(document).on('{$close}', function() { il.UI.table.presentation.collapseRow('{$id}'); return false; });".
				"il.UI.table.presentation.collapseRow('{$id}');";
		});
	}

}
