<?php declare(strict_types = 1);

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

namespace ILIAS\Services\Mail\Service;

use ILIAS\DI\Container;
use ILIAS\Services\Mail\AutoResponder\AutoResponderServiceImpl;
use ILIAS\Services\Mail\AutoResponder\AutoResponderService;
use ilMailTemplateService;
use ilObjUser;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Services\Mail\AutoResponder\AutoResponderDatabaseRepository;
use ilMailTemplateRepository;

class MailService
{
    protected Container $dic;

    public function __construct(Container $DIC)
    {
        $this->dic = $DIC;
        if (!isset($this->dic['mail.texttemplates.service'])) {
            $this->dic['mail.texttemplates.service'] = static function (Container $c) {
                return new ilMailTemplateService(new ilMailTemplateRepository($c->database()));
            };
        }
    }

    public function mime() : MimeMailService
    {
        return new MimeMailService($this->dic);
    }

    public function autoresponder() : AutoResponderService
    {
        return new AutoResponderServiceImpl(
            static function (int $usrId) : string {
                return ilObjUser::_lookupLogin($usrId);
            },
            (int) $this->dic->settings()->get('mail_auto_responder_idle_time'),
            false,
            [],
            new AutoResponderDatabaseRepository($this->dic->database()),
            (new DataFactory())->clock()->utc()
        );
    }

    public function textTemplatesService() : ilMailTemplateService
    {
        return $this->dic["mail.texttemplates.service"];
    }
}
