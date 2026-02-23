<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Mail;

use ILIAS\User\Profile\ChangeListeners\UserFieldAttributesChangeListener;
use ILIAS\User\Profile\Fields\Standard\SecondEmail;
use ILIAS\User\PropertyAttributes;
use ILIAS\Language\Language;

class ilMailUserFieldChangeListener implements UserFieldAttributesChangeListener
{
    public function isInterestedInField(): string
    {
        return SecondEmail::class;
    }

    public function isInterestedInAttribute(): PropertyAttributes
    {
        return PropertyAttributes::VisibleToUser;
    }

    public function getDescriptionForField(
        Language $lng,
        string $translated_field_name,
        string $translated_attribute_name
    ): string {
        $lng->loadLanguageModule('mail');
        return \sprintf(
            $lng->txt('usrFieldChange_second_mail_visible_in_personal_data'),
            $translated_attribute_name,
            $translated_field_name
        );
    }

    public function getComponentName(): string
    {
        return 'components/ILIAS/Mail';
    }
}
