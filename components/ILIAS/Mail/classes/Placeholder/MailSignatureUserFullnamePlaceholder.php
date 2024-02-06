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

namespace ILIAS\Mail\Placeholder;

use ilObjUser;
use ilLanguage;
use ilUserUtil;

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
        $placeholder[$this->getId()] = ilUserUtil::getNamePresentation($this->user_id);

        return $placeholder;
    }
}
