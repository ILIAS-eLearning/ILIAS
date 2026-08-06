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

namespace ILIAS\Init\ErrorHandling\Http;

use ilUtil;
use ilLanguage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Link;
use ilGlobalTemplate;
use ilGlobalTemplateInterface;
use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;

/**
 * Responder that renders a full ILIAS error page (UI-Framework MessageBox)
 * and sends it with the appropriate HTTP status code.
 *
 * Pass the DI {@see Container}: requires {@code lng}, {@code http}, and
 * {@code tpl}. Uses {@see UIServices} when {@code ui.factory} and
 * {@code ui.renderer} exist; otherwise {@see ilGlobalTemplateInterface} from
 * {@code tpl} ({@code tpl.error.html}: {@code plain_html_fallback} /
 * optional {@code plain_html_back_link}). If required entries are missing,
 * throws {@see UnableToRenderErrorPageResponderException}.
 *
 * The consumer MUST wrap {@see respond()} in a try-catch; on missing
 * dependencies catch {@see UnableToRenderErrorPageResponderException} and use
 * {@see PlainTextFallbackResponder}. For unexpected failures during bootstrap,
 * use {@see PlainTextFallbackResponder} as well.
 *
 * The error message is rendered via MessageBox::failure(). If a back target
 * (Data\Link) is provided, it is embedded into the MessageBox via withButtons().
 * Alternatively pass {@code $repository_href} (absolute or relative repository
 * entry URI): when {@code $back_target} is null, a link is built using
 * {@code error_back_to_repository}. If {@code $repository_href} is null or
 * empty, {@code ILIAS_HTTP_PATH} (when defined) plus the default repository
 * entry script are used.
 *
 * When {@code $error_message_lang_key} is non-empty, the visible message is
 * {@code language()->txt($error_message_lang_key)} after loading the error
 * module; otherwise {@code $error_message} is shown as-is.
 *
 * Use {@see translatedUserMessageOrNull()} for the same translation when the
 * HTML responder cannot be constructed (plain-text fallback).
 *
 * {@see GlobalScreenServices} may be null when ilCtrl fails during
 * {@see ilInitialisation::initILIAS()} before GlobalScreen is registered; the
 * external context claim is skipped in that case.
 */
readonly class ErrorPageResponder
{
    public const string ROUTING_FAILURE_MESSAGE_LANG_KEY = 'http_404_not_found';

    private ?GlobalScreenServices $global_screen;

    private ilLanguage $language;

    private HTTPServices $http;

    private DataFactory $data_factory;

    /** @var UIServices|ilGlobalTemplateInterface */
    private UIServices|ilGlobalTemplateInterface $shell;

    public function __construct(Container $dic)
    {
        if (!$dic->offsetExists('lng')
            || !$dic->offsetExists('http')
            || !$dic->offsetExists('tpl')
        ) {
            throw new UnableToRenderErrorPageResponderException();
        }

        $shell = ($dic->offsetExists('ui.factory') && $dic->offsetExists('ui.renderer'))
            ? $dic->ui()
            : $dic['tpl'];

        $this->global_screen = $dic->offsetExists('global_screen') ? $dic->globalScreen() : null;
        $this->language = $dic->language();
        $this->http = $dic->http();
        $this->shell = $shell;
        $this->data_factory = $dic->offsetExists(DataFactory::class)
            ? $dic[DataFactory::class]
            : new DataFactory();
    }

    /**
     * Resolved translated string for {@code $error_language_key} after loading
     * the error module, or null when {@code lng} is not registered (plain-text
     * fallback when {@see UnableToRenderErrorPageResponderException} is thrown).
     */
    public static function translatedUserMessageOrNull(Container $dic, string $error_language_key): ?string
    {
        if (!$dic->offsetExists('lng')) {
            return null;
        }
        $dic->language()->loadLanguageModule('error');

        return $dic->language()->txt($error_language_key);
    }

    public function respond(
        string $error_message,
        int $status_code,
        ?Link $back_target = null,
        ?string $repository_href = null,
        ?string $error_message_lang_key = null,
    ): never {
        $this->global_screen?->tool()->context()->claim()->external();

        $this->language->loadLanguageModule('error');

        $display_message = ($error_message_lang_key !== null && $error_message_lang_key !== '')
            ? $this->language->txt($error_message_lang_key)
            : $error_message;

        if ($back_target === null) {
            $effective_repository_href = ($repository_href !== null && $repository_href !== '')
                ? $repository_href
                : $this->defaultRepositoryEntryHref();
            $back_target = $this->data_factory->link(
                $this->language->txt('error_back_to_repository'),
                $this->data_factory->uri($effective_repository_href)
            );
        }

        $local_tpl = new ilGlobalTemplate('tpl.error.html', true, true);

        if ($this->shell instanceof UIServices) {
            $message_box = $this->shell->factory()->messageBox()->failure($display_message);

            if ($back_target !== null) {
                $message_box = $message_box->withButtons([
                    $this->shell->factory()->button()->standard(
                        $back_target->getLabel(),
                        ilUtil::secureUrl((string) $back_target->getURL())
                    ),
                ]);
            }

            $local_tpl->setCurrentBlock('msg_box');
            $local_tpl->setVariable(
                'MESSAGE_BOX',
                $this->shell->renderer()->render($message_box)
            );
            $local_tpl->parseCurrentBlock();
            $content_html = $local_tpl->get();
        } else {
            $local_tpl->setCurrentBlock('plain_html_fallback');
            $local_tpl->setVariable(
                'ERROR_MESSAGE',
                htmlspecialchars($display_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
            $local_tpl->parseCurrentBlock();
            $content_html = $local_tpl->get('plain_html_fallback');

            if ($back_target !== null) {
                $local_tpl->setCurrentBlock('plain_html_back_link');
                $local_tpl->setVariable(
                    'LINK_HREF',
                    ilUtil::secureUrl((string) $back_target->getURL())
                );
                $local_tpl->setVariable(
                    'LINK_TEXT',
                    htmlspecialchars($back_target->getLabel(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );
                $local_tpl->parseCurrentBlock();
                $content_html .= $local_tpl->get('plain_html_back_link');
            }
        }

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withStatus($status_code)
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html')
        );

        $main = $this->mainShellTemplate();
        $main->setContent($content_html);
        $main->printToStdout();

        $this->http->close();
    }

    private function mainShellTemplate(): ilGlobalTemplateInterface
    {
        return $this->shell instanceof UIServices
            ? $this->shell->mainTemplate()
            : $this->shell;
    }

    private function defaultRepositoryEntryHref(): string
    {
        return defined('ILIAS_HTTP_PATH')
            ? ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI'
            : '/ilias.php?baseClass=ilRepositoryGUI';
    }
}
