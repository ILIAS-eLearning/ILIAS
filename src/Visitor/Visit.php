<?php

namespace ILIAS\Visitor;

interface Visit {

	/**
	 * Visit an element.
	 */
	public function visit(Element $element);
}