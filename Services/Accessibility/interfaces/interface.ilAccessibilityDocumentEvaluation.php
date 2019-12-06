<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityDocumentEvaluation
 */
interface ilAccessibilityDocumentEvaluation
{
	/**
	 * @return ilAccessibilitySignableDocument
	 * @throws ilAccessibilityNoSignableDocumentFoundException
	 */
	public function document() : ilAccessibilitySignableDocument;

	/**
	 * @return bool
	 */
	public function hasDocument() : bool;
}