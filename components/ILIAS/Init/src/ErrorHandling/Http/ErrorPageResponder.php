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
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;

/**
 * Responder that renders a full ILIAS error page (UI-Framework MessageBox)
 * and sends it with the appropriate HTTP status code.
 *
 * Use this when the DI container and all ILIAS services are available.
 * The consumer MUST wrap the main logic in a try-catch and call
 * {@see respond()} in the catch block for expected errors (e.g., routing
 * failures). For unexpected errors during bootstrap, use
 * {@see PlainTextFallbackResponder} instead.
 *
 * The error message is rendered via MessageBox::failure(). If a back target
 * (Data\Link) is provided, it is embedded into the MessageBox via withButtons().
 */
class ErrorPageResponder
{
    public function __construct(
        private readonly GlobalScreenServices $global_screen,
        private readonly ilLanguage $language,
        private readonly UIServices $ui,
        private readonly HTTPServices $http
    ) {
    }

    public function respond(
        string $error_message,
        int $status_code,
        ?Link $back_target = null
    ): void {
        $this->global_screen->tool()->context()->claim()->external();
        $this->language->loadLanguageModule('error');

        $message_box = $this->ui->factory()->messageBox()->failure($error_message);

        if ($back_target !== null) {
            $ui_button = $this->ui->factory()->button()->standard(
                $back_target->getLabel(),
                ilUtil::secureUrl((string) $back_target->getURL())
            );
            $message_box = $message_box->withButtons([$ui_button]);
        }

        $local_tpl = new ilGlobalTemplate('tpl.error.html', true, true);
        $local_tpl->setCurrentBlock('msg_box');
        $local_tpl->setVariable(
            'MESSAGE_BOX',
            $this->ui->renderer()->render($message_box)
        );
        $local_tpl->parseCurrentBlock();

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withStatus($status_code)
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html')
        );

        $this->ui->mainTemplate()->setContent($local_tpl->get());
        $this->ui->mainTemplate()->printToStdout();

        $this->http->close();
    }
}
