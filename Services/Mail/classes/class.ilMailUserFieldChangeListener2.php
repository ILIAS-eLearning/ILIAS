<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\Mail;

use ILIAS\Services\User\ilUserFieldChangeListener;
use ILIAS\DI\Container;

/**
 * Class ilMailUserFieldChangeListener2
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMailUserFieldChangeListener2 extends ilUserFieldChangeListener
{
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->lng->loadLanguageModule("mail");
    }

    public function getDescriptionForField(string $fieldName, string $attribute) : ?string
    {
        if($fieldName === "email" && $attribute === "visible_email") {
            return sprintf(
                $this->dic->language()->txt("ABBC"),
                $fieldName, $attribute
            );
        }

        return null;
    }

    public function getComponentName() : string
    {
        return "Services/Mail2";
    }
}