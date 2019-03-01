<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree\Node;


/**
 * This describes a Tree Node
 */
interface AsyncNode
{
	/**
	 * Should this node load its children asyncronously?
	 */
	public function getAsyncLoading(): bool;

	/**
	 * Set URL for async loading.
	 */
	public function withAsyncURL(string $url): Simple;

	/**
	 * Get URL for async loading.
	 */
	public function getAsyncURL(): string;

}