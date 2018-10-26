<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\MainMenu\Item\Lost;

/**
 * Class TypeInformation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class TypeInformation {

	/**
	 * @var
	 */
	private $instance;
	/**
	 * @var string
	 */
	private $type = "";
	/**
	 * @var string
	 */
	private $type_name_for_presentation = "";
	/**
	 * @var TypeHandler
	 */
	private $type_handler;
	/**
	 * @var bool
	 */
	private $creation_prevented = false;


	/**
	 * TypeInformation constructor.
	 *
	 * @param string      $type
	 * @param string      $type_name_for_presentation
	 * @param TypeHandler $type_handler
	 */
	public function __construct(string $type, string $type_name_for_presentation, TypeHandler $type_handler = null) {
		$this->instance = new $type(new NullIdentification());
		$this->type = $type;
		$this->type_name_for_presentation = $type_name_for_presentation;
		$this->type_handler = $type_handler ? $type_handler : new BaseTypeHandler();
	}


	/**
	 * @return bool
	 */
	public function isCreationPrevented(): bool {
		return $this->creation_prevented;
	}


	/**
	 * @param bool $creation_prevented
	 */
	public function setCreationPrevented(bool $creation_prevented) {
		$this->creation_prevented = $creation_prevented;
	}


	/**
	 * @return bool
	 */
	public function isParent(): bool {
		if ($this->instance instanceof Lost) {
			return false;
		}

		return $this->instance instanceof isParent;
	}


	/**
	 * @return bool
	 */
	public function isTop(): bool {
		if ($this->instance instanceof Lost) {
			return false;
		}

		return $this->instance instanceof isTopItem;
	}


	/**
	 * @return bool
	 */
	public function isChild(): bool {
		if ($this->instance instanceof Lost) {
			return false;
		}

		return $this->instance instanceof isChild;
	}


	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}


	/**
	 * @param string $type
	 */
	public function setType(string $type) {
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getTypeNameForPresentation(): string {
		return $this->type_name_for_presentation;
	}


	/**
	 * @param string $type_name_for_presentation
	 */
	public function setTypeNameForPresentation(string $type_name_for_presentation) {
		$this->type_name_for_presentation = $type_name_for_presentation;
	}


	/**
	 * @return TypeHandler
	 */
	public function getTypeHandler(): TypeHandler {
		return $this->type_handler;
	}


	/**
	 * @param TypeHandler $type_handler
	 */
	public function setTypeHandler(TypeHandler $type_handler) {
		$this->type_handler = $type_handler;
	}
}
