<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Button\Close) {
            return $this->renderClose($component);
        } elseif ($component instanceof Component\Button\Toggle) {
            return $this->renderToggle($component);
        } elseif ($component instanceof Component\Button\Month) {
            return $this->renderMonth($component, $default_renderer);
        } else {
            return $this->renderButton($component, $default_renderer);
        }
    }


    /**
     * @param \ILIAS\UI\Component\Button\Button $component
     * @param \ILIAS\UI\Renderer                $default_renderer
     *
     * @return string
     */
    protected function renderButton(Component\Button\Button $component, RendererInterface $default_renderer)
    {
        if ($component instanceof Component\Button\Primary) {
            $tpl_name = "tpl.primary.html";
        }
        if ($component instanceof Component\Button\Standard) {
            $tpl_name = "tpl.standard.html";
        }
        if ($component instanceof Component\Button\Shy) {
            $tpl_name = "tpl.shy.html";
        }
        if ($component instanceof Component\Button\Tag) {
            $tpl_name = "tpl.tag.html";
        }
        if ($component instanceof Component\Button\Bulky) {
            $tpl_name = "tpl.bulky.html";
        }

        $tpl = $this->getTemplate($tpl_name, true, true);

        $action = $component->getAction();
        // The action is always put in the data-action attribute to have it available
        // on the client side, even if it is not available on rendering.
        if (is_string($action)) {
            $tpl->setCurrentBlock("with_data_action");
            $tpl->setVariable("ACTION", $action);
            $tpl->parseCurrentBlock();
        }

        $label = $component->getLabel();
        if ($label !== null) {
            $tpl->setVariable("LABEL", $component->getLabel());
        }
        if ($component->isActive()) {
            // The actions might also be a list of signals, these will be appended by
            // bindJavascript in maybeRenderId.
            if (is_string($action) && $action != "") {
                $component = $component->withAdditionalOnLoadCode(function ($id) use ($action) {
                    $action = str_replace("&amp;", "&", $action);

                    return "$('#$id').on('click', function(event) {
							window.location = '{$action}';
							return false;
					});";
                });
            }

            if ($component instanceof Component\Button\LoadingAnimationOnClick && $component->hasLoadingAnimationOnClick()) {
                $component = $component->withAdditionalOnLoadCode(function ($id) {
                    return "$('#$id').click(function(e) { $('#$id').addClass('il-btn-with-loading-animation'); $('#$id').addClass('disabled');});";
                });
            }
        } else {
            $tpl->touchBlock("disabled");
        }
        $aria_label = $component->getAriaLabel();
        if ($aria_label != null) {
            $tpl->setCurrentBlock("with_aria_label");
            $tpl->setVariable("ARIA_LABEL", $aria_label);
            $tpl->parseCurrentBlock();
        }
        if ($component->isAriaChecked()) {
            $tpl->setCurrentBlock("with_aria_checked");
            $tpl->setVariable("ARIA_CHECKED", "true");
            $tpl->parseCurrentBlock();
        }
        $this->maybeRenderId($component, $tpl);

        if ($component instanceof Component\Button\Tag) {
            $this->additionalRenderTag($component, $tpl);
        }

        if ($component instanceof Component\Button\Bulky) {
            $this->additionalRenderBulky($component, $default_renderer, $tpl);
        }


        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Button/button.js');
        $registry->register("./libs/bower/bower_components/moment/min/moment-with-locales.min.js");
        $registry->register("./libs/bower/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js");
    }

    protected function renderClose($component)
    {
        $tpl = $this->getTemplate("tpl.close.html", true, true);
        // This is required as the rendering seems to only create any output at all
        // if any var was set or block was touched.
        $tpl->setVariable("FORCE_RENDERING", "");
        $this->maybeRenderId($component, $tpl);
        return $tpl->get();
    }

    protected function renderToggle(Component\Button\Toggle $component)
    {
        $tpl = $this->getTemplate("tpl.toggle.html", true, true);

        $on_action = $component->getActionOn();
        $off_action = $component->getActionOff();

        $on_url = (is_string($on_action))
            ? $on_action
            : "";

        $off_url = (is_string($off_action))
            ? $off_action
            : "";

        $signals = [];

        foreach ($component->getTriggeredSignals() as $s) {
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }

        $signals = json_encode($signals);

        if ($component->isActive()) {
            $component = $component->withAdditionalOnLoadCode(function ($id) use ($on_url, $off_url, $signals) {
                $code = "$('#$id').on('click', function(event) {
						il.UI.button.handleToggleClick(event, '$id', '$on_url', '$off_url', $signals);
						return false; // stop event propagation
				});";
                //var_dump($code); exit;
                return $code;
            });
        } else {
            $tpl->touchBlock("disabled");
        }

        $is_on = $component->isOn();
        if ($is_on) {
            $tpl->touchBlock("on");
        }
        $label = $component->getLabel();
        if (!empty($label)) {
            $tpl->setCurrentBlock("with_label");
            $tpl->setVariable("LABEL", $label);
            $tpl->parseCurrentBlock();
        }
        $aria_label = $component->getAriaLabel();
        if ($aria_label != null) {
            $tpl->setCurrentBlock("with_aria_label");
            $tpl->setVariable("ARIA_LABEL", $aria_label);
            $tpl->parseCurrentBlock();
        }
        $this->maybeRenderId($component, $tpl);
        return $tpl->get();
    }

    protected function maybeRenderId(Component\Component $component, $tpl)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderMonth(Component\Button\Month $component, RendererInterface $default_renderer)
    {
        $def = $component->getDefault();

        for ($i = 1; $i<=12; $i++) {
            $this->toJS(array("month_" . str_pad($i, 2, "0", STR_PAD_LEFT) . "_short"));
        }

        $tpl = $this->getTemplate("tpl.month.html", true, true);

        $month = explode("-", $def);
        $tpl->setVariable("DEFAULT_LABEL", $this->txt("month_" . str_pad($month[0], 2, "0", STR_PAD_LEFT) . "_short") . " " . $month[1]);
        $tpl->setVariable("DEF_DATE", $month[0] . "/1/" . $month[1]);
        // see https://github.com/moment/moment/tree/develop/locale
        $lang_key = in_array($this->getLangKey(), array("ar", "bg", "cs", "da", "de", "el", "en", "es", "et", "fa", "fr", "hu", "it",
            "ja", "ka", "lt", "nl", "pl", "pt", "ro", "ru", "sk", "sq", "sr", "tr", "uk", "vi", "zh"))
            ? $this->getLangKey()
            : "en";
        if ($lang_key == "zh") {
            $lang_key = "zh-cn";
        }
        $tpl->setVariable("LANG", $lang_key);

        $id = $this->bindJavaScript($component);

        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
            $tpl->setVariable("JSID", $id);
        }

        return $tpl->get();
    }

    protected function additionalRenderTag(Component\Button\Tag $component, $tpl)
    {
        $tpl->touchBlock('rel_' . $component->getRelevance());

        $classes = trim(join(' ', $component->getClasses()));
        if ($classes !== '') {
            $tpl->setVariable("CLASSES", $classes);
        }

        $bgcol = $component->getBackgroundColor();
        if ($bgcol) {
            $tpl->setVariable("BGCOL", $bgcol->asHex());
        }
        $forecol = $component->getForegroundColor();
        if ($forecol) {
            $tpl->setVariable("FORECOL", $forecol->asHex());
        }
    }

    protected function additionalRenderBulky(Component\Button\Button $component, RendererInterface $default_renderer, $tpl)
    {
        $renderer = $default_renderer->withAdditionalContext($component);
        $tpl->setVariable("ICON_OR_GLYPH", $renderer->render($component->getIconOrGlyph()));
        $label = $component->getLabel();
        if ($label !== null) {
            $tpl->setVariable("LABEL", $label);
        }
        if ($component->isEngaged()) {
            $tpl->touchBlock("engaged");
            $tpl->setVariable("ARIA_PRESSED", 'true');
        } else {
            if (is_string($component->getAction())) {
                $tpl->setVariable("ARIA_PRESSED", 'undefined');
            } else {
                $tpl->setVariable("ARIA_PRESSED", 'false');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Button\Primary::class
        , Component\Button\Standard::class
        , Component\Button\Close::class
        , Component\Button\Shy::class
        , Component\Button\Month::class
        , Component\Button\Tag::class
        , Component\Button\Bulky::class
        , Component\Button\Toggle::class
        );
    }
}
