<?php

/**
 * cat-tms-patch start
 */

namespace ILIAS\TMS\Cockpit;

interface CockpitItem extends \CaT\Ente\Component {
	/**
	 * Get title of submenu entry
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get the tooltip for entry
	 *
	 * @return string
	 */
	public function getTooltip();

	/**
	 * Get the link for entry
	 *
	 * @return string
	 */
	public function getLink();

	/**
	 * Get the path to default icon
	 *
	 * @return string
	 */
	public function getIconPath();

	/**
	 * Get the path to the icon when entry is active
	 *
	 * @return string
	 */
	public function getActiveIconPath();

	/**
	 * Get the identifier to determine entry is active
	 *
	 * @return string
	 */
	public function getIdentifier();
}

/**
 * cat-tms-patch end
 */