<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Export;

use \ILIAS\DI\HTTPServices;
use \ILIAS\HTTP;
use \ILIAS\Filesystem\Stream\Streams;
use \ILIAS\DI\UIServices;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PrintProcessGUI
{
    /**
     * @var HTTPServices
     */
    protected $http;

    /**
     * @var callable[]
     */
    protected $injectors = [];

    /**
     * @var UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * PrintViewGUI constructor.
     * @param PrintViewProvider $provider
     * @param \ILIAS\HTTP\Services $http
     * @param UIServices   $ui
     * @param \ilLanguage  $lng
     * @param string|null  $body_class
     */
    public function __construct(
        PrintViewProvider $provider,
        \ILIAS\HTTP\Services $http,
        UIServices $ui,
        \ilLanguage $lng,
        string $body_class = null
    ) {
        $this->provider = $provider;
        $this->ui = $ui;
        $this->lng = $lng;
        $this->http = $http;
        $this->body_class = $body_class ?? "ilPrtfPdfBody";     // todo: move this class
        $lng->loadLanguageModule("exp");
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
    public function addTemplateInjector(callable $f) : void
    {
        $this->injectors[] = $f;
    }

    public function getModalElements(
        string $selection_action
    ) : \StdClass {
        $ui = $this->ui;
        $lng = $this->lng;

        $ui->mainTemplate()->addJavaScript("./Services/Form/js/Form.js");
        $modal = $ui->factory()->modal()->roundtrip(
            $lng->txt("exp_print_pdf"),
            $ui->factory()->legacy('some modal')
        )->withAsyncRenderUrl($selection_action);
        $print_button = $ui->factory()->button()->standard(
            $lng->txt("exp_print_pdf"),
            $modal->getShowSignal()
        );
        $elements = new \StdClass();
        $elements->button = $print_button;
        $elements->modal = $modal;

        return $elements;
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @throws HTTP\Response\Sender\ResponseSendingException
     */
    public function sendForm() : void
    {
        $form = $this->provider->getSelectionForm();
        $mb = $this->ui->factory()->messageBox()->info($this->lng->txt("exp_print_pdf_info"));
        $tpl = new \ilTemplate("tpl.print_view_selection.html", true, true, "Services/Export/Print");
        $form->setTarget("print_view");
        $tpl->setVariable("FORM", $form->getHTML());
        $modal = $this->ui->factory()->modal()->roundtrip(
            $this->lng->txt("exp_print_pdf"),
            $this->ui->factory()->legacy(
                $this->ui->renderer()->render($mb) .
                $tpl->get()
            )
        );

        $this->send($this->ui->renderer()->render($modal));
    }

    public function renderPrintView(int $content_style_id = 0) : string
    {
        $pages = $this->provider->getPages();
        $tpl = new \ilGlobalTemplate(
            "tpl.print_view.html",
            true,
            true,
            "Services/Export/Print"
        );

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


        $content = implode(
            '<p style="page-break-after:always;"></p>',
            $pages
        );

        $content = '<div class="ilInvisibleBorder">' . $content . '</div>';
        $content .= '<script type="text/javascript" language="javascript1.2">
				<!--
					il.Util.addOnLoad(function () {
						il.Util.print();
					});
				//-->
				</script>';


        $tpl->setVariable("CONTENT", $content);
        return $tpl->printToString();
    }

    /**
     * @throws HTTP\Response\Sender\ResponseSendingException
     */
    public function sendPrintView(int $content_style_id = 0) : void
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
