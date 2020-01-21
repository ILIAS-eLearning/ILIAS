<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

/**
 * Interface ScreenContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ScreenContext
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
     * @return ScreenContext
     */
    public function withReferenceId(ReferenceId $reference_id) : ScreenContext;


    /**
     * @param Collection $collection
     *
     * @return ScreenContext
     */
    public function withAdditionalData(Collection $collection) : ScreenContext;


    /**
     * @param string $key
     * @param        $value
     *
     * @return ScreenContext
     */
    public function addAdditionalData(string $key, $value) : ScreenContext;


    /**
     * @return Collection
     */
    public function getAdditionalData() : Collection;
}
