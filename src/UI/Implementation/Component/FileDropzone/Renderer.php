<?php
/**
 * Class Renderer
 *
 * Renderer implementation for file dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;

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
		// TODO: Implement render() method.
	}


	/**
	 * Renders the passed in standerd dropzone.
	 *
	 * @param \ILIAS\UI\Component\FileDropzone\Standard $standardDropzone the dropzone to render
	 *
	 * @return string the html representation of the passed in argument.
	 */
	private function renderStandardDropzone(\ILIAS\UI\Component\FileDropzone\Standard $standardDropzone) {

	}


	/**
	 * Renders the passed in wrapper dropzone.
	 *
	 * @param \ILIAS\UI\Component\FileDropzone\Wrapper $wrapperDropzone the dropzone to render
	 *
	 * @return string the html representation of the passed in argument.
	 */
	private function renderWrapperDropzone(\ILIAS\UI\Component\FileDropzone\Wrapper $wrapperDropzone) {

	}
}