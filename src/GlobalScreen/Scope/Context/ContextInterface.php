<?php namespace ILIAS\GlobalScreen\Scope\Context;

use ILIAS\Data\ReferenceId;

/**
 * Interface ContextInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ContextInterface {

	/**
	 * @return bool
	 */
	public function hasReferenceId(): bool;


	/**
	 * @return ReferenceId
	 */
	public function getReferenceId(): ReferenceId;


	/**
	 * @param ReferenceId $reference_id
	 *
	 * @return ContextInterface
	 */
	public function withReferenceId(ReferenceId $reference_id): ContextInterface;
}
