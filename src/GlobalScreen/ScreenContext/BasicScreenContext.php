<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class BasicScreenContext
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicScreenContext implements ScreenContext
{
    protected ReferenceId $reference_id;
    protected Collection $additional_data;
    protected string $context_identifier = '';
    
    /**
     * BasicScreenContext constructor.
     * @param string $context_identifier
     */
    public function __construct(string $context_identifier)
    {
        $this->context_identifier = $context_identifier;
        $this->additional_data = new Collection();
        $this->reference_id = new ReferenceId(0);
    }
    
    /**
     * @inheritDoc
     */
    public function hasReferenceId() : bool
    {
        return $this->reference_id->toInt() > 0;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferenceId() : ReferenceId
    {
        return $this->reference_id;
    }
    
    /**
     * @inheritDoc
     */
    public function withReferenceId(ReferenceId $reference_id) : ScreenContext
    {
        $clone = clone $this;
        $clone->reference_id = $reference_id;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withAdditionalData(Collection $collection) : ScreenContext
    {
        $clone = clone $this;
        $clone->additional_data = $collection;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function getAdditionalData() : Collection
    {
        return $this->additional_data;
    }
    
    /**
     * @inheritDoc
     */
    public function addAdditionalData(string $key, $value) : ScreenContext
    {
        $this->additional_data->add($key, $value);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getUniqueContextIdentifier() : string
    {
        return $this->context_identifier;
    }
}
