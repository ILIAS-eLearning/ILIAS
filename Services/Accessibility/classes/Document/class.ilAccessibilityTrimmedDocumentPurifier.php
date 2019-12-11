<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityTrimmedDocumentPurifier
 */
class ilAccessibilityTrimmedDocumentPurifier implements ilHtmlPurifierInterface
{
	/**
	 * @var ilHtmlPurifierInterface
	 */
	protected $inner;

	/**
	 * ilAccessibilityTrimmedDocumentPurifier constructor.
	 * @param ilHtmlPurifierInterface $inner
	 */
	public function __construct(ilHtmlPurifierInterface $inner)
	{
		$this->inner = $inner;
	}

	/**
	 * @inheritdoc
	 */
	public function purify(string $html) : string
	{
		return trim($this->inner->purify($html));
	}

	/**
	 * @inheritdoc
	 */
	public function purifyArray(array $htmlCollection) : array
	{
		foreach ($htmlCollection as $key => $html) {
			$htmlCollection[$key] = $this->purify($html);
		}

		return $htmlCollection;
	}
}