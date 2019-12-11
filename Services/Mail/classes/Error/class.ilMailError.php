<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailError
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailError
{
    /** @var string */
    protected $languageVariable = '';
    
    /** @var array */
    protected $placeHolderValues = [];

    /**
     * ilMailError constructor.
     * @param string $languageVariable
     * @param array  $placeHolderValues
     */
    public function __construct(string $languageVariable, array $placeHolderValues = [])
    {
        $this->languageVariable = $languageVariable;
        $this->placeHolderValues = $placeHolderValues;
    }

    /**
     * @return string
     */
    public function getLanguageVariable() : string
    {
        return $this->languageVariable;
    }

    /**
     * @return array
     */
    public function getPlaceHolderValues() : array
    {
        return $this->placeHolderValues;
    }
}
