<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailError
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailError
{
    protected string $languageVariable = '';
    protected array $placeHolderValues = [];

    public function __construct(string $languageVariable, array $placeHolderValues = [])
    {
        $this->languageVariable = $languageVariable;
        $this->placeHolderValues = $placeHolderValues;
    }

    public function getLanguageVariable() : string
    {
        return $this->languageVariable;
    }

    public function getPlaceHolderValues() : array
    {
        return $this->placeHolderValues;
    }
}
