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

namespace ILIAS\Mail\Signature;

use ilObjUser;
use ilLanguage;

class MailSignatureUserFullnamePlaceholder extends AbstractPlaceholderHandler
{
    public function __construct(ilLanguage $lng, private readonly int $user_id)
    {
        parent::__construct($lng);
    }

    public function getId(): string
    {
        return 'USER_FULLNAME';
    }

    public function addPlaceholder(array $placeholder): array
    {
        $full_name = ilObjUser::_lookupName($this->user_id);
        $full_name = $full_name['firstname'] . ' ' . $full_name['lastname'];
        $placeholder[$this->getId()] = $full_name;

        return $placeholder;
    }
}
