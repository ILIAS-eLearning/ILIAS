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
        return $this->processSignature($this->getPlaceholder(), $signature);
    }

    public function user(int $user_id): string
    {
        return $this->processSignature($this->getPlaceholder($user_id), new MailUserSignature($user_id));
    }

    private function processSignature(Placeholder $placeholder, Signature $signature): string
    {
        $placeholders = $placeholder->handle($signature);
        return $this->mustacheFactory->getBasicEngine()->render($signature->getSignature(), $placeholders);
    }

    public function getPlaceholder(int $user_id = 0): Placeholder
    {
        $p1 = new MailSignatureIliasUrlPlaceholder();
        $p2 = new MailSignatureInstallationNamePlaceholder();
        $p3 = new MailSignatureInstallationDescriptionPlaceholder();
        $p4 = new MailSignatureUserNamePlaceholder($user_id);
        $p5 = new MailSignatureUserFullnamePlaceholder($user_id);
        $p1->setNext($p2)->setNext($p3)->setNext($p4)->setNext($p5);
        return $p1;
    }
}
