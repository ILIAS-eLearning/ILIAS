<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree\Node;

use \ILIAS\UI\Component\Component;
use \ILIAS\UI\Component\Clickable;
use \ILIAS\UI\Component\Triggerable;
use \ILIAS\UI\Component\Signal;

/**
 * This describes a Tree Node
 */
interface Node extends Component, Clickable
{
	public function getLabel(): string;

	public function withExpanded(bool $expanded): Node;

	public function isExpanded(): bool;

	public function hasAsyncLoading(): bool;

	public function getToggleSignal(): Signal;
}
