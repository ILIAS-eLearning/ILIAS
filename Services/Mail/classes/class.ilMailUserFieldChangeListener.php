<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\Mail;

use ILIAS\Services\User\UserFieldAttributesChangeListener;
use ILIAS\DI\Container;

/**
 * Class ilMailUserFieldChangeListener
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMailUserFieldChangeListener extends UserFieldAttributesChangeListener
{
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->lng->loadLanguageModule("mail");
    }

    public function getDescriptionForField(string $fieldName, string $attribute) : ?string
    {
        if ($fieldName === "second_email" && $attribute === "visible_second_email") {
            return sprintf(
                $this->dic->language()->txt("usrFieldChange_second_mail_visible_in_personal_data"),
                $attribute,
                $fieldName
            );
        }

        return null;
    }

    public function getComponentName() : string
    {
        return "Services/Mail";
    }
}
