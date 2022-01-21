<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\User;

/**
 * Class InterestedUserFieldComponent
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldComponent
{
    private string $componentName;
    private string $description;

    public function __construct(string $componentName, string $description)
    {
        $this->componentName = $componentName;
        $this->description = $description;
    }

    public function getComponentName() : string
    {
        return $this->componentName;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
}
