<?php namespace ILIAS\NavigationContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinition;
use ILIAS\NavigationContext\AdditionalData\Collection;

/**
 * Interface ContextInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ContextInterface
{

    /**
     * @return string
     */
    public function getUniqueContextIdentifier() : string;


    /**
     * @return bool
     */
    public function hasReferenceId() : bool;


    /**
     * @return ReferenceId
     */
    public function getReferenceId() : ReferenceId;


    /**
     * @param ReferenceId $reference_id
     *
     * @return ContextInterface
     */
    public function withReferenceId(ReferenceId $reference_id) : ContextInterface;


    /**
     * @param Collection $collection
     *
     * @return ContextInterface
     */
    public function withAdditionalData(Collection $collection) : ContextInterface;


    /**
     * @param string $key
     * @param        $value
     *
     * @return ContextInterface
     */
    public function addAdditionalData(string $key, $value) : ContextInterface;


    /**
     * @return Collection
     */
    public function getAdditionalData() : Collection;


    /**
     * @return LayoutDefinition
     */
    public function getLayoutDefinition() : LayoutDefinition;


    /**
     * @param LayoutDefinition $view
     *
     * @return mixed
     */
    public function replaceLayoutDefinition(LayoutDefinition $view);
}
