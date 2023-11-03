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

namespace ILIAS\Repository\Modal;

use ILIAS\UI\Component\Modal;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Repository\HTTP\HTTPUtil;

class ModalAdapterGUI
{
    protected HTTPUtil $http_util;
    protected string $cancel_label;
    protected ?\ILIAS\Repository\Form\FormAdapterGUI $form = null;
    protected \ILIAS\Refinery\Factory $refinery;
    protected string $title = "";
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected ?array $ui_content = null;
    protected array $action_buttons = [];

    /**
     * @param string|array $class_path
     */
    public function __construct(
        string $title,
        string $cancel_label,
        HTTPUtil $http_util
    ) {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->title = $title;
        $this->cancel_label = $cancel_label;
        $this->http_util = $http_util;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function _send(string $output): void
    {
        $this->http_util->sendString($output);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function legacy(string $content): self
    {
        $this->ui_content = [$this->ui->factory()->legacy($content)];
        $this->form = null;
        return $this;
    }

    public function content(array $components): self
    {
        $this->ui_content = $components;
        $this->form = null;
        return $this;
    }

    public function button(string $text, string $url, bool $replace_modal = true, $onclick = ""): self
    {
        $target = $replace_modal
            ? "#"
            : $url;

        $button = $this->ui->factory()->button()->standard(
            $text,
            $target
        );

        if ($replace_modal) {
            $button = $button->withOnLoadCode(function ($id) use ($url, $onclick) {
                return
                    "$('#$id').click(function(event) { $onclick; il.repository.ui.redirect('$url'); return false;});";
            });
        } elseif ($onclick !== "") {
            $button = $button->withOnLoadCode(function ($id) use ($url, $onclick) {
                return "$('#$id').click(function(event) { $onclick });";
            });
        }
        $this->action_buttons[] = $button;
        return $this;
    }

    public function form(
        \ILIAS\Repository\Form\FormAdapterGUI $form,
        string $on_form_submit_click = ""
    ): self {
        if ($this->ctrl->isAsynch()) {
            $this->form = $form->asyncModal();
        } else {
            $this->form = $form->syncModal();
        }

        $async = $this->form->isSentAsync()
            ? "true"
            : "false";
        if ($on_form_submit_click === "") {
            $on_form_submit_click = "il.repository.ui.submitModalForm(event,$async); return false;";
        }
        $button = $this->ui->factory()->button()->standard(
            $this->form->getSubmitLabel(),
            "#"
        )->withOnLoadCode(function ($id) use ($on_form_submit_click) {
            return
                "$('#$id').click(function(event) {" . $on_form_submit_click . "});";
        });
        $this->action_buttons[] = $button;
        $this->ui_content = null;
        return $this;
    }

    protected function getModalWithContent(): Modal\RoundTrip
    {
        $modal = [];
        if (!is_null($this->form)) {
            $this->ui_content = [$this->ui->factory()->legacy($this->form->render())];
        }
        $modal = $this->ui->factory()->modal()->roundtrip($this->getTitle(), $this->ui_content);
        if (count($this->action_buttons) > 0) {
            $modal = $modal->withActionButtons($this->action_buttons);
        }
        if ($this->cancel_label !== "") {
            $modal = $modal->withCancelButtonLabel($this->cancel_label);
        }
        return $modal;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function send(): void
    {
        $modal = $this->getModalWithContent();
        $this->_send($this->ui->renderer()->renderAsync($modal));
    }

    public function renderAsyncTriggerButton(string $button_title, string $url, $shy = true): string
    {
        $ui = $this->ui;
        $components = $this->getAsyncTriggerButtonComponents(
            $button_title,
            $url,
            $shy
        );
        return $ui->renderer()->render($components);
    }

    public function getAsyncTriggerButtonComponents(string $button_title, string $url, $shy = true): array
    {
        $ui = $this->ui;
        $modal = $ui->factory()->modal()->roundtrip("", $ui->factory()->legacy(""));
        $url .= '&replaceSignal=' . $modal->getReplaceSignal()->getId();
        $modal = $modal->withAsyncRenderUrl($url);
        if ($shy) {
            $button = $ui->factory()->button()->shy($button_title, "#")
                         ->withOnClick($modal->getShowSignal());
        } else {
            $button = $ui->factory()->button()->standard($button_title, "#")
                         ->withOnClick($modal->getShowSignal());
        }
        return ["button" => $button, "modal" => $modal, "signal" => $modal->getShowSignal()->getId()];
    }

    public function getTriggerButtonComponents(string $button_title, $shy = true): array
    {
        $ui = $this->ui;

        $modal = $this->getModalWithContent();
        if ($shy) {
            $button = $ui->factory()->button()->shy($button_title, "#")
                         ->withOnClick($modal->getShowSignal());
        } else {
            $button = $ui->factory()->button()->standard($button_title, "#")
                         ->withOnClick($modal->getShowSignal());
        }
        return ["button" => $button, "modal" => $modal, "signal" => $modal->getShowSignal()->getId()];
    }
}
