<?php namespace ILIAS\GlobalScreen\Scope\Context;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Context\AdditionalData\Collection;

/**
 * Interface ContextInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ContextInterface {

	/**
	 * @return string
	 */
	public function getUniqueContextIdentifier(): string;


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


	/**
	 * @param Collection $collection
	 *
	 * @return ContextInterface
	 */
	public function withAdditionalData(Collection $collection): ContextInterface;


	/**
	 * @return Collection
	 */
	public function getAdditionalData(): Collection;
}
