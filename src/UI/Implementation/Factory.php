<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\NotImplementedException;

// TODO: This might cache the created factories.
use ILIAS\UI\Implementation\Component\SignalGenerator;

class Factory implements \ILIAS\UI\Factory
{
	/**
	 * @var Component\Counter\Factory
	 */
	protected $counter_factory;

	/**
	 * @var Component\Glyph\Factory
	 */
	protected $glyph_factory;

	/**
	 * @var Component\Button\Factory
	 */
	protected $button_factory;

	/**
	 * @var Component\Listing\Factory
	 */
	protected $listing_factory;

	/**
	 * @var Component\Image\Factory
	 */
	protected $image_factory;

	/**
	 * @var Component\Panel\Factory
	 */
	protected $panel_factory;

	/**
	 * @var Component\Modal\Factory
	 */
	protected $modal_factory;

	/**
	 * @var Component\Dropzone\Factory
	 */
	protected $dropzone_factory;

	/**
	 * @var Component\Popover\Factory
	 */
	protected $popover_factory;

	/**
	 * @var Component\Divider\Factory
	 */
	protected $divider_factory;

	/**
	 * @var Component\Link\Factory
	 */
	protected $link_factory;

	/**
	 * @var Component\Dropdown\Factory
	 */
	protected $dropdown_factory;

	/**
	 * @var Component\Item\Factory
	 */
	protected $item_factory;

	/**
	 * @var Component\Icon\Factory
	 */
	protected $icon_factory;

	/**
	 * @var Component\ViewControl\Factory
	 */
	protected $viewcontrol_factory;

	/**
	 * @var Component\Chart\Factory
	 */
	protected $chart_factory;

	/**
	 * @var Component\Input\Factory
	 */
	protected $input_factory;

	/**
	 * @var Component\Table\Factory
	 */
	protected $table_factory;

    /**
     * @var Component\MessageBox\Factory
     */
    protected $messagebox_factory;

	public function __construct(
		Component\Counter\Factory $counter_factory,
		Component\Glyph\Factory $glyph_factory,
		Component\Button\Factory $button_factory,
		Component\Listing\Factory $listing_factory,
		Component\Image\Factory	$image_factory,
		Component\Panel\Factory $panel_factory,
		Component\Modal\Factory $modal_factory,
		Component\Dropzone\Factory $dropzone_factory,
		Component\Popover\Factory $popover_factory,
		Component\Divider\Factory $divider_factory,
		Component\Link\Factory $link_factory,
		Component\Dropdown\Factory $dropdown_factory,
		Component\Item\Factory $item_factory,
		Component\Icon\Factory $icon_factory,
		Component\ViewControl\Factory $viewcontrol_factory,
		Component\Chart\Factory $chart_factory,
		Component\Input\Factory $input_factory,
		Component\Table\Factory $table_factory,
		Component\MessageBox\Factory $messagebox_factory
	) {
		$this->counter_factory = $counter_factory;
		$this->glyph_factory = $glyph_factory;
		$this->button_factory = $button_factory;
		$this->listing_factory = $listing_factory;
		$this->image_factory = $image_factory;
		$this->panel_factory = $panel_factory;
		$this->modal_factory = $modal_factory;
		$this->dropzone_factory = $dropzone_factory;
		$this->popover_factory = $popover_factory;
		$this->divider_factory = $divider_factory;
		$this->link_factory = $link_factory;
		$this->dropdown_factory = $dropdown_factory;
		$this->item_factory = $item_factory;
		$this->icon_factory = $icon_factory;
		$this->viewcontrol_factory = $viewcontrol_factory;
		$this->chart_factory = $chart_factory;
		$this->input_factory = $input_factory;
		$this->table_factor = $table_factory;
		$this->messagebox_factory = $messagebox_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function counter()
	{
		return $this->counter_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function glyph()
	{
		return $this->glyph_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function button()
	{
		return $this->button_factory;
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
		return $this->listing_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function image()
	{
		return $this->image_factory;
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
		return $this->panel_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function modal()
	{
		return $this->modal_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function dropzone()
	{
		return $this->dropzone_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function popover()
	{
		return $this->popover_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function divider()
	{
		return $this->divider_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function link()
	{
		return $this->link_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function dropdown()
	{
		return $this->dropdown_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function item()
	{
		return $this->item_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function icon()
	{
		return $this->icon_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function viewControl()
	{
		return $this->viewcontrol_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function breadcrumbs(array $crumbs)
	{
		return new Component\Breadcrumbs\Breadcrumbs($crumbs);
	}

	/**
	 * @inheritdoc
	 */
	public function chart()
	{
		return $this->chart_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function input()
	{
		return $this->input_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return $this->table_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function messageBox()
	{
        return $this->messagebox_factory;
	}
}
