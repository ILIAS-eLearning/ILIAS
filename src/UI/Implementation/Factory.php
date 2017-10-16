<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\NotImplementedException;

// TODO: This might cache the created factories.
use ILIAS\UI\Implementation\Component\SignalGenerator;

class Factory implements \ILIAS\UI\Factory
{

	/**
	 * @inheritdoc
	 */
	public function counter()
	{
		return new Component\Counter\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function glyph()
	{
		return new Component\Glyph\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function button()
	{
		return new Component\Button\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function card($title, \ILIAS\UI\Component\Image\Image $image = null)
	{
		return new Component\Card\Card($title, $image);
	}


	/**
	 * @inheritdoc
	 */
	public function deck(array $cards)
	{
		return new Component\Deck\Deck($cards, Component\Deck\Deck::SIZE_S);
	}


	/**
	 * @inheritdoc
	 */
	public function listing()
	{
		return new Component\Listing\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function image()
	{
		return new Component\Image\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function legacy($content)
	{
		return new Component\Legacy\Legacy($content);
	}


	/**
	 * @inheritdoc
	 */
	public function panel()
	{
		return new Component\Panel\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function modal()
	{
		return new Component\Modal\Factory(new SignalGenerator());
	}


	/**
	 * @inheritdoc
	 */
	public function dropzone() {
		return new Component\Dropzone\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function popover()
	{
		return new Component\Popover\Factory(new SignalGenerator());
	}


	/**
	 * @inheritdoc
	 */
	public function divider() {
		return new Component\Divider\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function link() {
		return new Component\Link\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function dropdown() {
		return new Component\Dropdown\Factory();
	}


	/**
	 * @inheritdoc
	 */
	public function item()
	{
		return new Component\Item\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function icon() {
		return new Component\Icon\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function viewControl()
	{
		return new Component\ViewControl\Factory(new SignalGenerator());
	}

	/**
	 * @inheritdoc
	 */
	public function breadcrumbs(array $crumbs) {
		return new Component\Breadcrumbs\Breadcrumbs($crumbs);
	}

	/**
	 * @inheritdoc
	 */
	public function chart()
	{
		return new Component\Chart\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function table()	{
		return new Component\Table\Factory(new SignalGenerator());
	}

}
