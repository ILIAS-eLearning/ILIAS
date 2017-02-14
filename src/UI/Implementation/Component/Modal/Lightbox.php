<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Modal\LightboxPage;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Lightbox extends Modal implements Component\Modal\Lightbox {

	/**
	 * @var LightboxPage[]
	 */
	protected $pages;


	/**
	 * @param LightboxPage|LightboxPage[] $pages
	 */
	public function __construct($pages) {
		$pages = $this->toArray($pages);
		$types = array( LightboxPage::class );
		$this->checkArgListElements('pages', $pages, $types);
		$this->pages = $pages;
	}


	/**
	 * @inheritdoc
	 */
	public function withPages(array $pages) {
		$types = array( LightboxPage::class );
		$this->checkArgListElements('pages', $pages, $types);
		$clone = clone $this;
		$clone->pages = $pages;

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function getPages() {
		return $this->pages;
	}
}
