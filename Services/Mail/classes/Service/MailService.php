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

namespace ILIAS\Mail\Service;

use ILIAS\DI\Container;
use ILIAS\Mail\Autoresponder\AutoresponderServiceImpl;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ilMailTemplateService;
use ilObjUser;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Autoresponder\AutoresponderDatabaseRepository;
use ilMailTemplateRepository;

class MailService
{
    protected Container $dic;

    public function __construct(Container $DIC)
    {
        $this->dic = $DIC;
        if (!isset($this->dic['mail.texttemplates.service'])) {
            $this->dic['mail.texttemplates.service'] = static function (Container $c): ilMailTemplateService {
                return new ilMailTemplateService(new ilMailTemplateRepository($c->database()));
            };
        }
    }

    public function mime(): MimeMailService
    {
        return new MimeMailService($this->dic);
    }

    public function autoresponder(): AutoresponderService
    {
        return new AutoresponderServiceImpl(
            (int) $this->dic->settings()->get('mail_auto_responder_idle_time'),
            false,
            new AutoresponderDatabaseRepository($this->dic->database()),
            (new DataFactory())->clock()->utc()
        );
    }

    public function textTemplates(): ilMailTemplateService
    {
        return $this->dic["mail.texttemplates.service"];
    }
}
