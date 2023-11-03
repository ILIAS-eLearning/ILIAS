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

namespace ILIAS\Export;

use ILIAS\HTTP;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\DI\UIServices;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PrintProcessGUI
{
    protected \ILIAS\GlobalScreen\Services $gs;
    protected \ilGlobalTemplateInterface $main_tpl;
    /**
     * @var callable[]
     */
    protected array $injectors = [];
    protected ?string $body_class = null;

    protected HTTP\Services $http;
    protected UIServices $ui;
    protected \ilLanguage $lng;
    protected PrintViewProvider $provider;

    /**
     * PrintViewGUI constructor.
     * @param PrintViewProvider    $provider
     * @param \ILIAS\HTTP\Services $http
     * @param UIServices           $ui
     * @param \ilLanguage          $lng
     * @param string|null          $body_class
     */
    public function __construct(
        PrintViewProvider $provider,
        \ILIAS\HTTP\Services $http,
        UIServices $ui,
        \ilLanguage $lng,
        string $body_class = null
    ) {
        global $DIC;

        $this->provider = $provider;
        $this->ui = $ui;
        $this->lng = $lng;
        $this->http = $http;
        $this->body_class = $body_class ?? "ilPrtfPdfBody";     // todo: move this class
        $this->lng->loadLanguageModule("exp");
        $this->gs = $DIC->globalScreen();
    }

    /**
     * Set output mode
     * @param string $a_val self::PRINT|self::OFFLINE
     */
    public function setOffline(bool $offline)
    {
        $this->provider->setOffline($offline);
    }

    // injectors are used to add css/js files to the template
    public function addTemplateInjector(callable $f): void
    {
        $this->injectors[] = $f;
    }

    public function getModalElements(
        string $selection_action
    ): \stdClass {
        $ui = $this->ui;
        $lng = $this->lng;

        $ui->mainTemplate()->addJavaScript("./Services/Form/js/Form.js");
        $modal = $ui->factory()->modal()->roundtrip(
            $this->lng->txt("exp_print_pdf"),
            $ui->factory()->legacy('some modal')
        )->withAsyncRenderUrl($selection_action);
        $print_button = $ui->factory()->button()->standard(
            $this->lng->txt("exp_print_pdf"),
            $modal->getShowSignal()
        );
        $elements = new \stdClass();
        $elements->button = $print_button;
        $elements->modal = $modal;

        return $elements;
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @throws HTTP\Response\Sender\ResponseSendingException
     */
    public function sendForm(): void
    {
        $form = $this->provider->getSelectionForm();
        $mb = $this->ui->factory()->messageBox()->info($this->lng->txt("exp_print_pdf_info"));
        $tpl = new \ilTemplate("tpl.print_view_selection.html", true, true, "Services/Export/Print");
        $form->setTarget("print_view");
        $tpl->setVariable("FORM", $form->getHTML());
        $tpl->setVariable("ON_SUBMIT_CODE", $this->provider->getOnSubmitCode());
        $modal = $this->ui->factory()->modal()->roundtrip(
            $this->lng->txt("exp_print_pdf"),
            $this->ui->factory()->legacy(
                $this->ui->renderer()->render($mb) .
                $tpl->get()
            )
        );

        $this->send($this->ui->renderer()->render($modal));
    }

    public function renderPrintView(int $content_style_id = 0): string
    {
        \iljQueryUtil::initjQuery();        // e.g. on survey print screens necessary
        $pages = $this->provider->getPages();
        $tpl = new \ilGlobalTemplate(
            "tpl.print_view.html",
            true,
            true,
            "Services/Export/Print"
        );

        // get all current resources from globalscreen and add them to our template
        foreach ($this->gs->layout()->meta()->getJs()->getItemsInOrderOfDelivery() as $js) {
            $path = explode("?", $js->getContent());
            $file = $path[0];
            $tpl->addJavaScript($file, $js->addVersionNumber());
        }
        foreach ($this->gs->layout()->meta()->getOnLoadCode()->getItemsInOrderOfDelivery() as $code) {
            $tpl->addOnLoadCode($code->getContent());
        }

        //\iljQueryUtil::initjQuery($tpl);

        foreach ($this->provider->getTemplateInjectors() as $f) {
            $f($tpl);
        }

        $tpl->setBodyClass($this->body_class);
        $tpl->addCss(\ilUtil::getStyleSheetLocation("filesystem"));
        $tpl->addCss(
            \ilObjStyleSheet::getContentStylePath(
                $content_style_id,
                false
            )
        );
        $tpl->addCss(\ilObjStyleSheet::getContentPrintStyle());
        $tpl->addCss(\ilObjStyleSheet::getSyntaxStylePath());

        $pb = ($this->provider->autoPageBreak())
            ? '<div style="page-break-after:always;"></div>'
            : "";

        $content = implode(
            $pb,
            $pages
        );

        $content = '<div class="ilInvisibleBorder">' . $content . '</div>';
        $tpl->addOnLoadCode("il.Util.print();");

        $tpl->setVariable("CONTENT", $content);
        return $tpl->printToString();
    }

    /**
     * @throws HTTP\Response\Sender\ResponseSendingException
     */
    public function sendPrintView(int $content_style_id = 0): void
    {
        $this->send($this->renderPrintView($content_style_id));
    }

    /**
     * Send
     * @param string $output
     * @throws HTTP\Response\Sender\ResponseSendingException
     */
    protected function send(string $output)
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }
}
