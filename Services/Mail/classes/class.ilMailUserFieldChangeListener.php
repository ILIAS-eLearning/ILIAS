<?php

declare(strict_types=1);

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
        $this->lng->loadLanguageModule('mail');
    }

    public function getDescriptionForField(string $fieldName, string $attribute): ?string
    {
        if ($fieldName === 'second_email' && $attribute === 'visible_second_email') {
            return sprintf(
                $this->dic->language()->txt('usrFieldChange_second_mail_visible_in_personal_data'),
                $attribute,
                $fieldName
            );
        }

        return null;
    }

    public function getComponentName(): string
    {
        return 'Services/Mail';
    }
}
