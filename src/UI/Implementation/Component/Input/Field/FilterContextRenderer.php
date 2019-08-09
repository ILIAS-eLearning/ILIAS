<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends AbstractComponentRenderer
{

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var $component Input
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Input\Field\Group) {
            /**
             * @var $component Group
             */
            return $this->renderFieldGroups($component, $default_renderer);
        }

        return $this->renderNoneGroupInput($component, $default_renderer);
    }


    /**
     * @param Component\Input\Field\Input $input
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderNoneGroupInput(Component\Input\Field\Input $input, RendererInterface $default_renderer)
    {
        $input_tpl = null;

        if ($input instanceof Component\Input\Field\Text) {
            $input_tpl = $this->getTemplate("tpl.text.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Select) {
            $input_tpl = $this->getTemplate("tpl.select.html", true, true);
        } else {
            throw new \LogicException("Cannot render '" . get_class($input) . "'");
        }

        return $this->renderProxyFieldWithContext($input_tpl, $input, $default_renderer);
    }


    /**
     * @param Group             $group
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer)
    {
        $inputs = "";
        $input_labels = array();
        foreach ($group->getInputs() as $input) {
            $inputs .= $default_renderer->render($input);
            $input_labels[] = $input->getLabel();
        }
        if (!$group->isDisabled()) {
            $inputs .= $this->renderAddField($input_labels, $default_renderer);
        }

        return $inputs;
    }


    /**
     * @param Template $input_tpl
     * @param Input    $input
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderProxyFieldWithContext(Template $input_tpl, Input $input, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);

        if ($input->isDisabled()) {
            $remove_glyph = $f->symbol()->glyph()->remove()->withUnavailableAction();
        } else {
            $remove_glyph = $f->symbol()->glyph()->remove("")->withAdditionalOnLoadCode(function ($id) {
                $code = "$('#$id').on('click', function(event) {
							il.UI.filter.onRemoveClick(event, '$id');
							return false; // stop event propagation
					});";
                return $code;
            });
        }

        $tpl->setCurrentBlock("addon_left");
        $tpl->setVariable("LABEL", $input->getLabel());
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $this->renderProxyField($input_tpl, $input, $default_renderer));
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("addon_right");
        $tpl->setVariable("DELETE", $default_renderer->render($remove_glyph));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    /**
     * @param Template $tpl
     * @param Input    $input
     * @param RendererInterface    $default_renderer
     *
     * @return string
     */
    protected function renderProxyField(Template $input_tpl, Input $input, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.filter_field.html", true, true);

        $content = $this->renderInputField($input_tpl, $input);
        $popover = $f->popover()->standard($f->legacy($content))->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));

        $prox = new ProxyFilterField();
        if (!$input->isDisabled()) {
            $prox = $prox->withOnClick($popover->getShowSignal());
            $tpl->touchBlock("tabindex");
        }

        $this->maybeRenderId($prox, $tpl);
        return $tpl->get();
    }

    /**
     * @param Template $tpl
     * @param Input    $input
     * @param RendererInterface    $default_renderer
     *
     * @return string
     */
    protected function renderInputField(Template $tpl, Input $input)
    {
        switch (true) {
            case ($input instanceof Text):
                $tpl->setVariable("NAME", $input->getName());

                if ($input->getValue() !== null) {
                    $tpl->setCurrentBlock("value");
                    $tpl->setVariable("VALUE", $input->getValue());
                    $tpl->parseCurrentBlock();
                }
                if ($input->isDisabled()) {
                    $tpl->setCurrentBlock("disabled");
                    $tpl->setVariable("DISABLED", "disabled");
                    $tpl->parseCurrentBlock();
                }
                break;

            case ($input instanceof Select):
                $tpl->setVariable("NAME", $input->getName());
                $tpl = $this->renderSelectInput($tpl, $input);
                break;

        }

        foreach ($input->getTriggeredSignals() as $s) {
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }
        $signals = json_encode($signals);

        $input = $input->withAdditionalOnLoadCode(function ($id) use ($signals) {
            $code = "il.UI.input.setSignalsForId('$id', $signals);";
            return $code;
        });
        $input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
        $this->maybeRenderId($input, $tpl);

        return $tpl->get();
    }

    public function renderSelectInput(Template $tpl, Select $input)
    {
        if ($input->isDisabled()) {
            $tpl->setCurrentBlock("disabled");
            $tpl->setVariable("DISABLED", "disabled");
            $tpl->parseCurrentBlock();
        }
        $value = $input->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", "selected");
        }
        if ($input->isRequired()) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }
        $tpl->setVariable("VALUE", null);
        $tpl->setVariable("VALUE_STR", "-");
        $tpl->parseCurrentBlock();
        //rest of options.
        foreach ($input->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if ($value == $option_key) {
                $tpl->setVariable("SELECTED", "selected");
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }

    /**
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderAddField(array $input_labels, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);
        $add_tpl = $this->getTemplate("tpl.filter_add_list.html", true, true);

        $links = array();
        foreach ($input_labels as $label) {
            $links[] = $f->button()->shy($label, "")->withAdditionalOnLoadCode(function ($id) {
                $code = "$('#$id').on('click', function(event) {
						il.UI.filter.onAddClick(event, '$id');
						return false; // stop event propagation
				});";
                return $code;
            });
        }
        $add_tpl->setVariable("LIST", $default_renderer->render($f->listing()->unordered($links)));
        $list = $f->legacy($add_tpl->get());
        $popover = $f->popover()->standard($list)->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));
        $add = $f->button()->bulky($f->symbol()->glyph()->add(), "", "")->withOnClick($popover->getShowSignal());

        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $default_renderer->render($add));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    /**
     * @param Component\JavascriptBindable $component
     * @param Template                     $tpl
     */
    protected function maybeRenderId(Component\JavascriptBindable $component, $tpl)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock("id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Input/Container/filter.js');
        $registry->register('./src/UI/templates/js/Input/Field/input.js');
    }


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return [
            Component\Input\Field\Text::class,
            Component\Input\Field\Select::class,
            Component\Input\Field\Group::class
        ];
    }
}
