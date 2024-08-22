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

use ilIniFile;
use ilMustacheFactory;
use ILIAS\Mail\Signature\Signature;
use ILIAS\Mail\Signature\MailInstallationSignature;
use ILIAS\Mail\Signature\MailUserSignature;
use ilLanguage;
use ilSetting;
use ILIAS\Mail\Placeholder\Placeholder;
use ILIAS\Mail\Placeholder\MailSignatureIliasUrlPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureInstallationNamePlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureInstallationDescriptionPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureUserLoginPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureUserFullnamePlaceholder;

class MailSignatureService
{
    public function __construct(
        private readonly ilMustacheFactory $mustacheFactory,
        private readonly ilIniFile $client_ini_file,
        private readonly ilLanguage $lng,
        private readonly ilSetting $settings
    ) {
    }

    public function installation(): string
    {
        return $this->processSignature(
            $this->getPlaceholder(),
            new MailInstallationSignature($this->settings)
        );
    }

    public function user(int $user_id): string
    {
        return $this->processSignature(
            $this->getPlaceholder($user_id),
            new MailUserSignature($this->settings)
        );
    }

    private function processSignature(Placeholder $placeholder, Signature $signature): string
    {
        $placeholders = $placeholder->handle($signature);

        return "\n\n\n" . $this->mustacheFactory->getBasicEngine()->render($signature->getSignature(), $placeholders);
    }

    public function getPlaceholder(int $user_id = 0): Placeholder
    {
        $ilias_url_ph = new MailSignatureIliasUrlPlaceholder($this->lng);
        $installation_name_ph = new MailSignatureInstallationNamePlaceholder($this->lng, $this->client_ini_file);
        $installation_description_ph = new MailSignatureInstallationDescriptionPlaceholder($this->lng);
        $user_name_ph = new MailSignatureUserLoginPlaceholder($this->lng, $user_id);
        $user_fullname_ph = new MailSignatureUserFullnamePlaceholder($this->lng, $user_id);
        $ilias_url_ph
            ->setNext($installation_name_ph)
            ->setNext($installation_description_ph)
            ->setNext($user_name_ph)
            ->setNext($user_fullname_ph);

        return $ilias_url_ph;
    }
}
