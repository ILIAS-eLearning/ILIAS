<?php

namespace ILIAS\Visitor;

interface Element {

	/**
	 * Accept a visitor.
	 */
	public function accept(Visit $visitor);
}