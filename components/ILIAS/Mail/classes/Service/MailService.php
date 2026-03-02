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
use ILIAS\Mail\Autoresponder\AutoresponderServiceImpl;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;
use ilMailTemplateService;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Autoresponder\AutoresponderDatabaseRepository;
use ilMailTemplateRepository;
use ilMailTemplateServiceInterface;
use ILIAS\Mail\TemplateEngine\Mustache\MustacheTemplateEngineFactory;

class MailService
{
    public function __construct(protected Container $dic)
    {
        $this->dic[self::class] = $this;
    }

    public function populate(): void
    {
        if (!isset($this->dic[ilMailTemplateServiceInterface::class])) {
            $this->dic[ilMailTemplateServiceInterface::class] = static function (Container $c): ilMailTemplateServiceInterface {
                return new ilMailTemplateService(
                    new ilMailTemplateRepository($c->database()),
                    $c->mail()->templateEngineFactory()
                );
            };
        }

        if (!isset($this->dic[TemplateEngineFactoryInterface::class])) {
            $this->dic[TemplateEngineFactoryInterface::class] = static function (Container $c): TemplateEngineFactoryInterface {
                return $c->mail()->templateEngineFactory();
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
            (int) $this->dic->settings()->get(
                'mail_auto_responder_idle_time',
                (string) AutoresponderService::AUTO_RESPONDER_DEFAULT_IDLE_TIME
            ),
            false,
            new AutoresponderDatabaseRepository($this->dic->database()),
            (new DataFactory())->clock()->utc()
        );
    }

    public function textTemplates(): ilMailTemplateServiceInterface
    {
        return $this->dic[ilMailTemplateServiceInterface::class];
    }

    public function placeholderResolver(): \ilMailTemplatePlaceholderResolver
    {
        return new \ilMailTemplatePlaceholderResolver(
            $this->templateEngineFactory()->getBasicEngine()
        );
    }

    public function placeholderToEmptyResolver(): \ilMailTemplatePlaceholderToEmptyResolver
    {
        return new \ilMailTemplatePlaceholderToEmptyResolver();
    }

    public function templateEngineFactory(): TemplateEngineFactoryInterface
    {
        if (!isset($this->dic['mail.template_engine.factory'])) {
            $this->dic['mail.template_engine.factory'] = static function (Container $c): MustacheTemplateEngineFactory {
                return new MustacheTemplateEngineFactory();
            };
        }

        return $this->dic['mail.template_engine.factory'];
    }

    public function signature(): MailSignatureService
    {
        return new MailSignatureService(
            $this->templateEngineFactory(),
            $this->dic->clientIni(),
            $this->dic->language(),
            $this->dic->settings()
        );
    }
}
