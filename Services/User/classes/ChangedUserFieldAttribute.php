<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\User;

/**
 * Class ChangedUserFieldAttribute
 * @package Services\User
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttribute
{
    private string $attributeName;
    private string $oldValue;
    private string $newValue;

    public function __construct(string $attributeName, string $oldValue, string $newValue)
    {
        $this->attributeName = $attributeName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function getAttributeName() : string
    {
        return $this->attributeName;
    }

    public function getOldValue() : string
    {
        return $this->oldValue;
    }

    public function getNewValue() : string
    {
        return $this->newValue;
    }
}
