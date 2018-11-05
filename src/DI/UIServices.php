<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilTemplate;

/**
 * Class UIServices
 *
 * Provides fluid interface to RBAC services.
 *
 * @package ILIAS\DI
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @since   5.2
 */
final class UIServices {

	/**
	 * @var Container
	 */
	protected $container;


	/**
	 * UIServices constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}


	/**
	 * Get the factory that crafts UI components.
	 *
	 * @return Factory
	 */
	public function factory(): Factory {
		return $this->container["ui.factory"];
	}


	/**
	 * Get a renderer for UI components.
	 *
	 * @return Renderer
	 */
	public function renderer(): Renderer {
		return $this->container["ui.renderer"];
	}


	/**
	 * Get the ILIAS main template.
	 *
	 * @return ilTemplate
	 */
	public function mainTemplate(): ilTemplate {
		return $this->container["tpl"];
	}
}
