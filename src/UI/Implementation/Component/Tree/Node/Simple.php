<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node\Simple as ISimple;
use ILIAS\UI\Component\Icon\Icon;

/**
 * A very simple Tree-Node
 */
class Simple extends Node implements ISimple
{
	/**
	 * @var string
	 */
	protected $asynch_url = '';

	/**
	 * @var Icon|null
	 */
	protected $icon;

	public function __construct(string $label, Icon $icon=null)
	{
		parent::__construct($label);
		$this->icon = $icon;
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function getIcon()
	{
		return $this->icon;
	}

	/**
	 * @inheritdoc
	 */
	public function getAsyncLoading(): bool
	{
		return $this->getAsyncURL() != '';
	}

	/**
	 * @inheritdoc
	 */
	public function withAsyncURL(string $url): ISimple
	{
		$clone = clone $this;
		$clone->asynch_url = $url;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAsyncURL(): string
	{
		return $this->asynch_url;
	}
}