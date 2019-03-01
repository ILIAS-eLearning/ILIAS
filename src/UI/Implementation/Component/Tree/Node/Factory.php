<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node as INode;

class Factory implements INode\Factory
{
	/**
	 * @inheritdoc
	 */
	public function simple(string $label): INode\Simple
	{
		return new Simple($label);
	}

}
