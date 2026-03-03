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
use ilMailTemplateService;
use ilMailMimeSenderFactory;
use ilMailTemplateRepository;
use ilMailMimeTransportFactory;
use ilMailTemplateServiceInterface;
use ILIAS\Data\Factory as DataFactory;
use ilMailTemplatePlaceholderResolver;
use ilMailTemplatePlaceholderToEmptyResolver;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\Mail\Autoresponder\AutoresponderServiceImpl;
use ILIAS\Mail\Autoresponder\AutoresponderDatabaseRepository;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;
use ILIAS\Mail\TemplateEngine\Mustache\MustacheTemplateEngineFactory;

class MailService
{
    public function __construct(protected Container $dic)
    {
    }

    public static function init(Container $container): void
    {
        $container[self::class] = static function (Container $c): self {
            return new self($c);
        };

        $container[ilMailTemplateServiceInterface::class] = static function (Container $c): ilMailTemplateServiceInterface {
            return new ilMailTemplateService(
                new ilMailTemplateRepository($c->database()),
                $c->mail()->templateEngineFactory()
            );
        };

        $container[TemplateEngineFactoryInterface::class] = static function (Container $c): TemplateEngineFactoryInterface {
            return $c->mail()->templateEngineFactory();
        };

        $container[MimeMailService::class] = static function (Container $c): MimeMailService {
            return new MimeMailService($c);
        };

        $container['mail.mime.transport.factory'] = static function (Container $c): ilMailMimeTransportFactory {
            return new ilMailMimeTransportFactory($c->settings(), $c->event());
        };

        $container['mail.mime.sender.factory'] = static function (Container $c): ilMailMimeSenderFactory {
            return new ilMailMimeSenderFactory(
                $c->settings(),
                $c->mail()->templateEngineFactory()
            );
        };

        $container['mail.texttemplates.service'] = static function (Container $c): ilMailTemplateService {
            return new ilMailTemplateService(
                new ilMailTemplateRepository($c->database()),
                $c->mail()->templateEngineFactory()
            );
        };

        $container['mail.template.placeholder.resolver'] = static function (Container $c): ilMailTemplatePlaceholderResolver {
            return new ilMailTemplatePlaceholderResolver(
                $c->mail()->templateEngineFactory()->getBasicEngine()
            );
        };

        $container[AutoresponderService::class] = static function (Container $c): AutoresponderService {
            return new AutoresponderServiceImpl(
                (int) $c->settings()->get(
                    'mail_auto_responder_idle_time',
                    (string) AutoresponderService::AUTO_RESPONDER_DEFAULT_IDLE_TIME
                ),
                false,
                new AutoresponderDatabaseRepository($c->database()),
                (new DataFactory())->clock()->utc()
            );
        };

        $container[ilMailTemplatePlaceholderResolver::class] = static function (Container $c): ilMailTemplatePlaceholderResolver {
            return new ilMailTemplatePlaceholderResolver(
                $c->mail()->templateEngineFactory()->getBasicEngine()
            );
        };

        $container[ilMailTemplatePlaceholderToEmptyResolver::class] = static function (Container $c): ilMailTemplatePlaceholderToEmptyResolver {
            return new ilMailTemplatePlaceholderToEmptyResolver();
        };

        $container['mail.template_engine.factory'] = static function (Container $c): MustacheTemplateEngineFactory {
            return new MustacheTemplateEngineFactory();
        };

        $container['mail.signature.service'] = static function (Container $c): MailSignatureService {
            return new MailSignatureService(
                $c->mail()->templateEngineFactory(),
                $c->clientIni(),
                $c->language(),
                $c->settings()
            );
        };
    }

    public function mime(): MimeMailService
    {
        return $this->dic[MimeMailService::class];
    }

    public function autoresponder(): AutoresponderService
    {
        return $this->dic[AutoresponderService::class];
    }

    public function textTemplates(): ilMailTemplateServiceInterface
    {
        return $this->dic[ilMailTemplateServiceInterface::class];
    }

    public function placeholderResolver(): ilMailTemplatePlaceholderResolver
    {
        return $this->dic[ilMailTemplatePlaceholderResolver::class];
    }

    public function placeholderToEmptyResolver(): ilMailTemplatePlaceholderToEmptyResolver
    {
        return $this->dic[ilMailTemplatePlaceholderToEmptyResolver::class];
    }

    public function templateEngineFactory(): TemplateEngineFactoryInterface
    {
        return $this->dic['mail.template_engine.factory'];
    }

    public function signature(): MailSignatureService
    {
        return $this->dic['mail.signature.service'];
    }
}
