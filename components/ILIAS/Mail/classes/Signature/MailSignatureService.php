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

namespace ILIAS\Mail\Service;

use ilUtil;
use ilObjUser;
use ilIniFile;
use ilMustacheFactory;
use ilSetting;
use ILIAS\Mail\Signature\Placeholder;
use ILIAS\Mail\Signature\MailSignatureIliasUrlPlaceholder;
use ILIAS\Mail\Signature\MailSignatureInstallationNamePlaceholder;
use ILIAS\Mail\Signature\MailSignatureInstallationUrlPlaceholder;
use ILIAS\Mail\Signature\MailSignatureUserFullnamePlaceholder;
use ILIAS\Mail\Signature\MailSignatureUserNamePlaceholder;
use ILIAS\Mail\Signature\Signature;
use ILIAS\Mail\Signature\MailInstallationSignature;
use ILIAS\Mail\Signature\MailUserSignature;
use ILIAS\Mail\Signature\MailSignatureInstallationDescriptionPlaceholder;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sign;

class MailSignatureService
{
    private ilMustacheFactory $mustacheFactory;
    private Placeholder $placeholder_chain;

    public function __construct()
    {
        global $DIC;
        $this->mustacheFactory = $DIC->mail()->mustacheFactory();
    }

    public function installation(): string
    {
        $signature = new MailInstallationSignature();
        return $this->processSignature($signature->getPlaceholder(), $signature);
    }

    public function user(int $user_id): string
    {
        $signature = new MailUserSignature($user_id);
        return $this->processSignature($signature->getPlaceholder(), $signature);
    }

    private function processSignature(Placeholder $placeholder, Signature $signature): string
    {
        $placeholders = $placeholder->handle($signature);
        return $this->mustacheFactory->getBasicEngine()->render($signature->getSignature(), $placeholders);
    }
}
