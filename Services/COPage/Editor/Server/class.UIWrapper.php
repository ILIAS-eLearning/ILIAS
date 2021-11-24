<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class UIWrapper
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(\ILIAS\DI\UIServices $ui, \ilLanguage $lng)
    {
        $this->ui = $ui;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("copg");
    }

    /**
     * Get multi button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getButton(
        string $content,
        string $type,
        string $action,
        array $data = null,
        string $component = ""
    ) : \ILIAS\UI\Component\Button\Standard {
        $ui = $this->ui;
        $f = $ui->factory();
        $b = $f->button()->standard($content, "");
        if ($data === null) {
            $data = [];
        }
        $b = $b->withOnLoadCode(
            function ($id) use ($type, $data, $action, $component) {
                $code = "document.querySelector('#$id').setAttribute('data-copg-ed-type', '$type');
                         document.querySelector('#$id').setAttribute('data-copg-ed-component', '$component');
                         document.querySelector('#$id').setAttribute('data-copg-ed-action', '$action')";
                foreach ($data as $key => $val) {
                    $code .= "\n document.querySelector('#$id').setAttribute('data-copg-ed-par-$key', '$val');";
                }
                return $code;
            }
        );
        return $b;
    }

    public function getRenderedInfoBox($text)
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $m = $f->messageBox()->info($text);
        return $ui->renderer()->renderAsync($m);
    }

    public function getRenderedFailureBox()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $m = $f->messageBox()->failure($this->lng->txt("copg_an_error_occured"))
            ->withLinks([$f->link()->standard($this->lng->txt("copg_details"), "#")]);

        return $ui->renderer()->renderAsync($m);
    }

    /**
     * Get rendered button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getRenderedButton(string $content, string $type, string $action, array $data = null,
        string $component = "") : string
    {
        $ui = $this->ui;
        $b = $this->getButton($content, $type, $action, $data, $component);
        return $ui->renderer()->renderAsync($b);
    }

    /**
     * Get multi actions
     * @return string
     */
    public function getRenderedButtonGroups($groups)
    {
        $ui = $this->ui;
        $r = $ui->renderer();

        $tpl = new \ilTemplate("tpl.editor_button_group.html", true, true, "Services/COPage");

        foreach ($groups as $buttons) {
            foreach ($buttons as $action => $lng_key) {
                $tpl->setCurrentBlock("button");
                $b = $this->getButton($this->lng->txt($lng_key), "multi", $action);
                $tpl->setVariable("BUTTON", $r->renderAsync($b));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("section");
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Get rendered form footer
     * @return string
     */
    public function getRenderedFormFooter($buttons)
    {
        $ui = $this->ui;
        $r = $ui->renderer();

        $tpl = new \ilTemplate("tpl.form_footer.html", true, true, "Services/COPage");

        $html = "";
        foreach ($buttons as $b) {
            $html .= $ui->renderer()->renderAsync($b);
        }

        $tpl->setVariable("BUTTONS", $html);

        return $tpl->get();
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @param                    $buttons
     * @return string
     */
    public function getRenderedForm(\ilPropertyFormGUI $form, $buttons)
    {
        $form->clearCommandButtons();
        $cnt = 0;
        foreach ($buttons as $button) {
            $cnt++;
            $form->addCommandButton("", $button[2], "cmd-" . $cnt);
        }
        $html = $form->getHTML();
        $cnt = 0;
        foreach ($buttons as $button) {
            $cnt++;
            $html = str_replace(
                "id='cmd-" . $cnt . "'",
                " data-copg-ed-type='form-button' data-copg-ed-action='" . $button[1] . "' data-copg-ed-component='" . $button[0] . "'",
                $html
            );
        }
        return $html;
    }

    /**
     * Send whole page as response
     * @return Response
     */
    public function sendPage($page_gui, $updated) : Response
    {
        $error = null;
        $rendered_content = null;
        $last_change = null;

        if ($updated !== true) {
            if (is_array($updated)) {
                $error = implode("<br />", $updated);
            } elseif (is_string($updated)) {
                $error = $updated;
            } else {
                $error = print_r($updated, true);
            }
        } else {
            $page_gui->setOutputMode(\ilPageObjectGUI::EDIT);
            $page_gui->setDefaultLinkXml(); // fixes #31225
            $page_data = $page_gui->showPage();
            $pc_model = $page_gui->getPageObject()->getPCModel();
            $last_change = $page_gui->getPageObject()->getLastChange();
        }

        $data = new \stdClass();
        $data->renderedContent = $page_data;
        $data->pcModel = $pc_model;
        $data->error = $error;
        if ($last_change) {
            $lu = new \ilDateTime($last_change, IL_CAL_DATETIME);
            \ilDatePresentation::setUseRelativeDates(false);
            $data->last_update = \ilDatePresentation::formatDate($lu, true);
        }
        return new Response($data);
    }

    public function getRenderedViewControl($actions) : string
    {
        $ui = $this->ui;
        $cnt = 0;
        $view_modes = [];
        foreach ($actions as $act) {
            $cnt++;
            $view_modes[$act[2]] = "cmd-" . $cnt;
        }
        $vc = $ui->factory()->viewControl()->mode($view_modes, "");
        $html = $ui->renderer()->render($vc);
        $cnt = 0;
        foreach ($actions as $act) {
            $cnt++;
            $html = str_replace(
                'data-action="cmd-' . $cnt . '"',
                " data-copg-ed-type='view-control' data-copg-ed-action='" . $act[1] . "' data-copg-ed-component='" . $act[0] . "'",
                $html
            );
        }
        $html = str_replace("id=", "data-id=", $html);
        return $html;
    }


    /**
     * Get multi button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getLink(
        string $content,
        string $component,
        string $type,
        string $action,
        array $data = null
    ) : \ILIAS\UI\Component\Button\Shy {
        $ui = $this->ui;
        $f = $ui->factory();
        $l = $f->button()->shy($content, "");
        if ($data === null) {
            $data = [];
        }
        $l = $l->withOnLoadCode(
            function ($id) use ($component, $type, $data, $action) {
                $code = "document.querySelector('#$id').setAttribute('data-copg-ed-component', '$component');
                         document.querySelector('#$id').setAttribute('data-copg-ed-type', '$type');
                         document.querySelector('#$id').setAttribute('data-copg-ed-action', '$action')";
                foreach ($data as $key => $val) {
                    $code .= "\n document.querySelector('#$id').setAttribute('data-copg-ed-par-$key', '$val');";
                }
                return $code;
            }
        );
        return $l;
    }

    /**
     * Get rendered button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getRenderedLink(string $content, string $component, string $type, string $action, array $data = null) : string
    {
        $ui = $this->ui;
        $l = $this->getLink($content, $component, $type, $action, $data);
        return $ui->renderer()->renderAsync($l);
    }

    /**
     * Get rendered icon
     * @param
     * @return
     */
    public function getRenderedIcon($type)
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $r = $ui->renderer();
        $i = $f->symbol()->icon()->standard($type, $type, 'medium');
        return $r->render($i);
    }
}
