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

use ilIniFile;
use ilLanguage;

class MailSignatureInstallationDescriptionPlaceholder extends AbstractPlaceholderHandler
{
    private readonly ilIniFile $clientIniFile;

    public function __construct(ilLanguage $lng)
    {
        global $DIC;
        $this->clientIniFile = $DIC['ilClientIniFile'];
        parent::__construct($lng);
    }

    public function getId(): string
    {
        return 'INSTALLATION_DESC';
    }

    public function addPlaceholder(array $placeholder): array
    {
        $placeholder[$this->getId()] = $this->clientIniFile->readVariable('client', 'name');

        return $placeholder;
    }
}
