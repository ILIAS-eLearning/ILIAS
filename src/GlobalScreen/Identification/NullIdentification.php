<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class NullIdentification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullIdentification implements IdentificationInterface {

	/**
	 * @inheritDoc
	 */
	public function serialize() {
		return "";
	}


	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized) {
		return;
	}


	/**
	 * @inheritDoc
	 */
	public function getClassName(): string {
		return "Null";
	}


	/**
	 * @inheritDoc
	 */
	public function getInternalIdentifier(): string {
		return "Null";
	}


	/**
	 * @inheritDoc
	 */
	public function getProviderNameForPresentation(): string {
		return "Null";
	}
}
