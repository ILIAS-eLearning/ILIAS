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
use ILIAS\Data\Link;
use ilGlobalTemplate;
use ilGlobalTemplateInterface;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;

/**
 * Responder that renders a full ILIAS error page (UI-Framework MessageBox)
 * and sends it with the appropriate HTTP status code.
 *
 * Pass {@see UIServices} as {@code $shell} for a MessageBox. If {@code ui.factory}
 * / {@code ui.renderer} are not available, pass {@see ilGlobalTemplateInterface}
 * (e.g. {@code $DIC['tpl']}) — {@code tpl.error.html} is then filled via
 * {@code plain_html_fallback} (simple alert + link).
 *
 * Use this when the DI container and all ILIAS services are available.
 * The consumer MUST wrap the main logic in a try-catch and call
 * {@see respond()} in the catch block for expected errors (e.g., routing
 * failures). For unexpected errors during bootstrap, use
 * {@see PlainTextFallbackResponder} instead.
 *
 * The error message is rendered via MessageBox::failure(). If a back target
 * (Data\Link) is provided, it is embedded into the MessageBox via withButtons().
 *
 * {@see GlobalScreenServices} may be null when ilCtrl fails during
 * {@see ilInitialisation::initILIAS()} before GlobalScreen is registered; the
 * external context claim is skipped in that case.
 */
readonly class ErrorPageResponder
{
    /**
     * @param UIServices|ilGlobalTemplateInterface $shell {@see UIServices} or main page template without UI stack.
     */
    public function __construct(
        private ?GlobalScreenServices $global_screen,
        private ilLanguage $language,
        private HTTPServices $http,
        private UIServices|ilGlobalTemplateInterface $shell,
    ) {
    }

    public function respond(
        string $error_message,
        int $status_code,
        ?Link $back_target = null
    ): never {
        $this->global_screen?->tool()->context()->claim()->external();

        $this->language->loadLanguageModule('error');

        $local_tpl = new ilGlobalTemplate('tpl.error.html', true, true);

        if ($this->shell instanceof UIServices) {
            $message_box = $this->shell->factory()->messageBox()->failure($error_message);

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
        } else {
            $local_tpl->setCurrentBlock('plain_html_fallback');
            $local_tpl->setVariable(
                'ERROR_MESSAGE',
                htmlspecialchars($error_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
            if ($back_target !== null) {
                $local_tpl->setVariable(
                    'LINK_HREF',
                    ilUtil::secureUrl((string) $back_target->getURL())
                );
                $local_tpl->setVariable(
                    'LINK_TEXT',
                    htmlspecialchars($back_target->getLabel(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );
            } else {
                $local_tpl->setVariable('LINK_HREF', '');
                $local_tpl->setVariable('LINK_TEXT', '');
            }
            $local_tpl->parseCurrentBlock();
        }

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withStatus($status_code)
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html')
        );

        $main = $this->mainShellTemplate();
        $main->setContent($local_tpl->get());
        $main->printToStdout();

        $this->http->close();
    }

    private function mainShellTemplate(): ilGlobalTemplateInterface
    {
        return $this->shell instanceof UIServices
            ? $this->shell->mainTemplate()
            : $this->shell;
    }
}
