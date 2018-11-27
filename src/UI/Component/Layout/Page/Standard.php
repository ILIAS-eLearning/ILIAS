<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component as C;

/**
 * This describes the Page
 */
interface Standard extends C\Component {

	/**
	 * @return 	Component|Component[]
	 */
	public function getContent();

	/**
	 * @return 	ILIAS\UI\Component\Layout\Metabar | null
	 */
	public function getMetabar();

	/**
	 * @return 	ILIAS\UI\Component\Layout\Sidebar | null
	 */
	public function getMainbar();

	/**
	 * @return 	ILIAS\UI\Component\Breadcrumbs | null
	 */
	public function getBreadcrumbs();

}
