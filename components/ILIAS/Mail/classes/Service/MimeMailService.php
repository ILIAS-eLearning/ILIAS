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

use ILIAS\DI\Container;
use ilMailMimeTransportFactory;
use ilMailMimeSenderFactory;
use ilMailTemplateRepository;
use ilMailTemplateService;
use ilMailTemplatePlaceholderResolver;
use ilMustacheFactory;

class MimeMailService
{
    public function __construct(protected Container $dic)
    {
        if (!isset($this->dic['mail.mime.transport.factory'])) {
            $this->dic['mail.mime.transport.factory'] = static function (Container $c): ilMailMimeTransportFactory {
                return new ilMailMimeTransportFactory($c->settings(), $c->event());
            };
        }

        if (!isset($this->dic['mail.mime.sender.factory'])) {
            $this->dic['mail.mime.sender.factory'] = static function (Container $c): ilMailMimeSenderFactory {
                return new ilMailMimeSenderFactory(
                    $c->settings(),
                    $c->mail()->mustacheFactory()
                );
            };
        }

        if (!isset($this->dic['mail.texttemplates.service'])) {
            $this->dic['mail.texttemplates.service'] = static function (Container $c): ilMailTemplateService {
                return new ilMailTemplateService(
                    new ilMailTemplateRepository($c->database()),
                    $c["mail.mustache.factory"]
                );
            };
        }

        if (!isset($this->dic['mail.template.placeholder.resolver'])) {
            $this->dic['mail.template.placeholder.resolver'] = static function (Container $c): ilMailTemplatePlaceholderResolver {
                return new ilMailTemplatePlaceholderResolver(
                    $c["mail.mustache.factory"]->getBasicEngine()
                );
            };
        }

        if (!isset($this->dic['mail.mustache.factory'])) {
            $this->dic['mail.mustache.factory'] = static function (Container $c): ilMustacheFactory {
                return new ilMustacheFactory();
            };
        }
    }

    public function transportFactory(): ilMailMimeTransportFactory
    {
        return $this->dic['mail.mime.transport.factory'];
    }

    public function senderFactory(): ilMailMimeSenderFactory
    {
        return $this->dic['mail.mime.sender.factory'];
    }
}
