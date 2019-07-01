<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Tool\Context\AdditionalData\Collection;

/**
 * Interface ToolContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ToolContext
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
     * @return ToolContext
     */
    public function withReferenceId(ReferenceId $reference_id) : ToolContext;


    /**
     * @param Collection $collection
     *
     * @return ToolContext
     */
    public function withAdditionalData(Collection $collection) : ToolContext;


    /**
     * @param string $key
     * @param        $value
     *
     * @return ToolContext
     */
    public function addAdditionalData(string $key, $value) : ToolContext;


    /**
     * @return Collection
     */
    public function getAdditionalData() : Collection;
}
