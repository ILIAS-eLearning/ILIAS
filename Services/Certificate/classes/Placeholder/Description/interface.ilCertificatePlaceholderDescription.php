<?php

interface ilCertificatePlaceholderDescription
{
	/**
	 * This method MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function getPlaceholderDescriptions();

	/**
	 * @return string - HTML that can used to be displayed in the GUI
	 */
	public function createPlaceholderHtmlDescription();
}
