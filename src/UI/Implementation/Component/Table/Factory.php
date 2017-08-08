<?php
namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

/**
 * Implementation of factory for tables
 *
 * @author Nils Haagen <nhaageng@concepts-and-training.de>
 */
class Factory implements T\Factory {

	/**
	 * @inheritdoc
	 */
	public function presentation($title, array $view_controls, array $rows) {
		return new Presentation($title, $view_controls, $rows);
	}


	/**
	 * @inheritdoc
	 */
	public function presentationRow($title_field) {
		return new PresentationRow($title_field);
	}

}
