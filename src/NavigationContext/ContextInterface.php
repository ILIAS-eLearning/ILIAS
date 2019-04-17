<?php namespace ILIAS\NavigationContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\View\View;
use ILIAS\NavigationContext\AdditionalData\Collection;

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


	/**
	 * @return View
	 */
	public function getView(): View;


	/**
	 * @param View $view
	 *
	 * @return mixed
	 */
	public function replaceView(View $view);
}
