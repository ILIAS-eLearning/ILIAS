<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;

class Factory implements IMenu\Factory
{
	/**
	 * @inheritdoc
	 */
	public function drilldown($label, array $items): IMenu\Drilldown
	{
		return new Drilldown($label, $items);
	}

	/**
	 * @inheritdoc
	 */
	public function sub($label, array $items): IMenu\Sub
	{
		return new Sub($label, $items);
	}

}
