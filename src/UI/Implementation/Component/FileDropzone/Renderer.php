<?php
/**
 * Class Renderer
 *
 * Renderer implementation for file dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer {

	/**
	 * @var $renderer DefaultRenderer
	 */
	private $renderer;

	/**
	 * @inheritDoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			\ILIAS\UI\Component\FileDropzone\Standard::class,
			\ILIAS\UI\Component\FileDropzone\Wrapper::class
		);
	}


	/**
	 * @inheritdoc
	 */
	public function render(Component $component, \ILIAS\UI\Renderer $default_renderer) {
		$this->checkComponent($component);

		$this->renderer = $default_renderer;

		if ($component instanceof \ILIAS\UI\Component\FileDropzone\Wrapper) {
			return $this->renderWrapperDropzone($component);
		}

		if ($component instanceof \ILIAS\UI\Component\FileDropzone\Standard) {
			return $this->renderStandardDropzone($component);
		}
	}


	/**
	 * @inheritDoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register("./src/UI/templates/js/FileDropzone/dropzone.js");
	}


	/**
	 * Renders the passed in standerd dropzone.
	 *
	 * @param \ILIAS\UI\Component\FileDropzone\Standard $standardDropzone the dropzone to render
	 *
	 * @return string the html representation of the passed in argument.
	 */
	private function renderStandardDropzone(\ILIAS\UI\Component\FileDropzone\Standard $standardDropzone) {

		$dropzoneId = $this->createId();

		$tpl = $this->getTemplate("tpl.standard-file-dropzone.html", true, true);
		$tpl->setVariable("ID", $dropzoneId);

		if (strcmp($standardDropzone->getDefaultMessage(), "") !== 0) {
			$tpl->setCurrentBlock("with_message");
			$tpl->setVariable("MESSAGE", $standardDropzone->getDefaultMessage());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * Renders the passed in wrapper dropzone.
	 *
	 * @param \ILIAS\UI\Component\FileDropzone\Wrapper $wrapperDropzone the dropzone to render
	 *
	 * @return string the html representation of the passed in argument.
	 */
	private function renderWrapperDropzone(\ILIAS\UI\Component\FileDropzone\Wrapper $wrapperDropzone) {

		$dropzoneId = $this->createId();

		$tpl = $this->getTemplate("tpl.wrapper-file-dropzone.html", true, true);
		$tpl->setVariable("ID", $dropzoneId);
		$tpl->setVariable("CONTENT", $this->getContentAsHtml($wrapperDropzone->getContent()));

		return $tpl->get();
	}


	/**
	 * Renders each component of the passed in array.
	 *
	 * @param Component[] $componentList an array of ILIAS UI components
	 *
	 * @return string the passed in components as html
	 */
	private function getContentAsHtml(array $componentList) {

		$contentHmtl = "";

		foreach ($componentList as $component) {
			$contentHmtl .= $this->renderer->render($component);
		}

		return $contentHmtl;
	}
}