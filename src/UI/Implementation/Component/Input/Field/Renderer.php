<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\Password;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Field\MultiSelect;
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
class Renderer extends AbstractComponentRenderer
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
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Input/Field/dependantGroup.js');
        $registry->register('./libs/bower/bower_components/typeahead.js/dist/typeahead.bundle.js');
        $registry->register('./libs/bower/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js');
        $registry->register('./libs/bower/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css');
        $registry->register('./src/UI/templates/js/Input/Field/tagInput.js');
        $registry->register('./src/UI/templates/js/Input/Field/textarea.js');
        $registry->register('./src/UI/templates/js/Input/Field/radioInput.js');
    }


    /**
     * @param Component\Input\Field\Input $input
     *
     * @return string
     */
    protected function renderNoneGroupInput(Component\Input\Field\Input $input, RendererInterface $default_renderer)
    {
        $input_tpl = null;
        $id = null;
        $dependant_group_html = null;

        if ($input instanceof Component\Input\Field\DependantGroupProviding) {
            if ($input->getDependantGroup()) {
                $dependant_group_html = $default_renderer->render($input->getDependantGroup());
                $id = $this->bindJavaScript($input);
            }
        }

        if ($input instanceof Component\Input\Field\Text) {
            $input_tpl = $this->getTemplate("tpl.text.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Numeric) {
            $input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Checkbox) {
            $input_tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Tag) {
            $input_tpl = $this->getTemplate("tpl.tag_input.html", true, true);
        } elseif ($input instanceof Password) {
            $input_tpl = $this->getTemplate("tpl.password.html", true, true);
        } elseif ($input instanceof Select) {
            $input_tpl = $this->getTemplate("tpl.select.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Textarea) {
            $input_tpl = $this->getTemplate("tpl.textarea.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Radio) {
            return $this->renderRadioField($input, $default_renderer);
        } elseif ($input instanceof MultiSelect) {
            $input_tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        } else {
            throw new \LogicException("Cannot render '" . get_class($input) . "'");
        }

        $html = $this->renderInputFieldWithContext($input_tpl, $input, $id, $dependant_group_html);
        return $html;
    }


    /**
     * @param Group             $group
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer)
    {
        if ($group instanceof Component\Input\Field\DependantGroup) {
            /**
             * @var $group DependantGroup
             */
            return $this->renderDependantGroup($group, $default_renderer);
        } else {
            if ($group instanceof Component\Input\Field\Section) {
                /**
                 * @var $group Section
                 */
                return $this->renderSection($group, $default_renderer);
            }
        }
        $inputs = "";
        foreach ($group->getInputs() as $input) {
            $inputs .= $default_renderer->render($input);
        }

        return $inputs;
    }

    /**
     * @param Component\JavascriptBindable $component
     * @param                              $tpl
     */
    protected function maybeRenderId(Component\JavascriptBindable $component, Template $tpl)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
    }


    /**
     * @param Section           $section
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderSection(Section $section, RendererInterface $default_renderer)
    {
        $section_tpl = $this->getTemplate("tpl.section.html", true, true);
        $section_tpl->setVariable("LABEL", $section->getLabel());

        if ($section->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $section->getByline());
            $section_tpl->parseCurrentBlock();
        }

        if ($section->getError() !== null) {
            $section_tpl->setCurrentBlock("error");
            $section_tpl->setVariable("ERROR", $section->getError());
            $section_tpl->parseCurrentBlock();
        }
        $inputs_html = "";

        foreach ($section->getInputs() as $input) {
            $inputs_html .= $default_renderer->render($input);
        }
        $section_tpl->setVariable("INPUTS", $inputs_html);

        return $section_tpl->get();
    }


    /**
     * @param DependantGroup    $dependant_group
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderDependantGroup(DependantGroup $dependant_group, RendererInterface $default_renderer)
    {
        $dependant_group_tpl = $this->getTemplate("tpl.dependant_group.html", true, true);

        $toggle = $dependant_group->getToggleSignal();
        $show = $dependant_group->getShowSignal();
        $hide = $dependant_group->getHideSignal();
        $init = $dependant_group->getInitSignal();

        $dependant_group = $dependant_group->withAdditionalOnLoadCode(
            function ($id) use ($toggle, $show, $hide, $init) {
                return "il.UI.Input.dependantGroup.init('$id',{toggle:'$toggle',show:'$show',hide:'$hide',init:'$init'});";
            }
        );

        /**
         * @var $dependant_group DependantGroup
         */
        $id = $this->bindJavaScript($dependant_group);
        $dependant_group_tpl->setVariable("ID", $id);

        $inputs_html = "";

        foreach ($dependant_group->getInputs() as $input) {
            $inputs_html .= $default_renderer->render($input);
        }
        $dependant_group_tpl->setVariable("CONTENT", $inputs_html);

        return $dependant_group_tpl->get();
    }


    /**
     * @param Template $input_tpl
     * @param Input    $input
     * @param null     $id
     * @param null     $dependant_group_html
     *
     * @return string
     */
    protected function renderInputFieldWithContext(Template $input_tpl, Input $input, $id = null, $dependant_group_html = null)
    {
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);
        /**
         * TODO: should we throw an error in case for no name or render without name?
         *
         * if(!$input->getName()){
         * throw new \LogicException("Cannot render '".get_class($input)."' no input name given.
         * Is there a name source attached (is this input packed into a container attaching
         * a name source)?");
         * } */
        if ($input->getName()) {
            $tpl->setVariable("NAME", $input->getName());
        } else {
            $tpl->setVariable("NAME", "");
        }

        $tpl->setVariable("LABEL", $input->getLabel());
        $tpl->setVariable("INPUT", $this->renderInputField($input_tpl, $input, $id));

        if ($input->getByline() !== null) {
            $tpl->setCurrentBlock("byline");
            $tpl->setVariable("BYLINE", $input->getByline());
            $tpl->parseCurrentBlock();
        }

        if ($input->isRequired()) {
            $tpl->touchBlock("required");
        }

        if ($input->getError() !== null) {
            $tpl->setCurrentBlock("error");
            $tpl->setVariable("ERROR", $input->getError());
            $tpl->parseCurrentBlock();
        }

        if ($dependant_group_html !== null) {
            $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        }


        return $tpl->get();
    }


    /**
     * @param Template $tpl
     * @param Input    $input
     * @param          $id
     *
     * @return string
     */
    protected function renderInputField(Template $tpl, Input $input, $id)
    {
        if ($input instanceof Component\Input\Field\Password) {
            $id = $this->additionalRenderPassword($tpl, $input);
        }

        if ($input instanceof Textarea) {
            $tpl = $this->renderTextareaField($tpl, $input);
        }

        $tpl->setVariable("NAME", $input->getName());

        switch (true) {
            case ($input instanceof Text):
            case ($input instanceof Checkbox):
            case ($input instanceof Numeric):
            case ($input instanceof Password):
            case ($input instanceof Textarea):
                $tpl->setVariable("NAME", $input->getName());

                if ($input->getValue() !== null) {
                    $tpl->setCurrentBlock("value");
                    $tpl->setVariable("VALUE", $input->getValue());
                    $tpl->parseCurrentBlock();
                }
                if ($id) {
                    $tpl->setCurrentBlock("id");
                    $tpl->setVariable("ID", $id);
                    $tpl->parseCurrentBlock();
                }
                break;
            case ($input instanceof Select):
                $tpl = $this->renderSelectInput($tpl, $input);
                break;
            case ($input instanceof MultiSelect):
                $tpl = $this->renderMultiSelectInput($tpl, $input);
                break;

            case ($input instanceof Tag):
                $configuration = $input->getConfiguration();
                $input = $input->withAdditionalOnLoadCode(
                    function ($id) use ($configuration) {
                        $encoded = json_encode($configuration);

                        return "il.UI.Input.tagInput.init('{$id}', {$encoded});";
                    }
                );
                $id = $this->bindJavaScript($input);
                /**
                 * @var $input \ILIAS\UI\Implementation\Component\Input\Field\Tag
                 */
                $tpl->setVariable("ID", $id);
                $tpl->setVariable("NAME", $input->getName());
                if ($input->getValue()) {
                    $value = $input->getValue();
                    $tpl->setVariable("VALUE_COMMA_SEPARATED", implode(",", $value));
                    foreach ($value as $tag) {
                        $tpl->setCurrentBlock('existing_tags');
                        $tpl->setVariable("FIELD_ID", $id);
                        $tpl->setVariable("FIELD_NAME", $input->getName());
                        $tpl->setVariable("TAG_NAME", $tag);
                        $tpl->parseCurrentBlock();
                    }
                }
                break;
        }

        return $tpl->get();
    }

    public function renderSelectInput(Template $tpl, Select $input)
    {
        $value = $input->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", "selected");
        }
        if ($input->isRequired()) {
            $tpl->setVariable("DISABLED", "disabled");
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

    public function renderMultiSelectInput(Template $tpl, MultiSelect $input) : Template
    {
        $value = $input->getValue();
        $name = $input->getName();

        foreach ($input->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("NAME", $name);
            $tpl->setVariable("VALUE", $opt_value);
            $tpl->setVariable("LABEL", $opt_label);

            if ($value && in_array($opt_value, $value)) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }

            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }


    /*
     * Render revelation-glyphs for password and register signals/functions
     * @param Template $tpl
     * @param Password $input
     *
     * @return string | false
     */
    protected function additionalRenderPassword(Template $tpl, Component\Input\Field\Password $input)
    {
        $id = false;
        if ($input->getRevelation()) {
            global $DIC;
            $f = $this->getUIFactory();
            $renderer = $DIC->ui()->renderer();

            $input = $input->withResetSignals();
            $sig_reveal = $input->getRevealSignal();
            $sig_mask = $input->getMaskSignal();

            $input = $input->withAdditionalOnLoadCode(function ($id) use ($sig_reveal, $sig_mask) {
                return
                    "$(document).on('{$sig_reveal}', function() {
						$('#{$id}').addClass('revealed');
						$('#{$id}')[0].getElementsByTagName('input')[0].type='text';
					});" .
                    "$(document).on('{$sig_mask}', function() {
						$('#{$id}').removeClass('revealed');
						$('#{$id}')[0].getElementsByTagName('input')[0].type='password';
					});"
                    ;
            });
            $id = $this->bindJavaScript($input);

            $glyph_reveal = $f->glyph()->eyeopen("#")
                ->withOnClick($sig_reveal);
            $glyph_mask = $f->glyph()->eyeclosed("#")
                ->withOnClick($sig_mask);
            $tpl->setCurrentBlock('revelation');
            $tpl->setVariable('PASSWORD_REVEAL', $renderer->render($glyph_reveal));
            $tpl->setVariable('PASSWORD_MASK', $renderer->render($glyph_mask));
            $tpl->parseCurrentBlock();
        }
        return $id;
    }

    protected function renderTextareaField(Template $tpl, Textarea $input)
    {
        if ($input->isLimited()) {
            $this->toJS("ui_chars_remaining");
            $this->toJS("ui_chars_min");
            $this->toJS("ui_chars_max");

            $counter_id_prefix = "textarea_feedback_";
            $min = $input->getMinLimit();
            $max = $input->getMaxLimit();

            $input = $input->withOnLoadCode(function ($id) use ($counter_id_prefix, $min, $max) {
                return "il.UI.textarea.changeCounter('$id','$counter_id_prefix','$min','$max');";
            });

            $textarea_id = $this->bindJavaScript($input);
            $tpl->setVariable("ID", $textarea_id);
            $tpl->setVariable("FEEDBACK_MAX_LIMIT", $max);
        }

        return $tpl;
    }


    /**
     * @param Radio $input
     * @param RendererInterface    $default_renderer
     *
     * @return string
     */
    protected function renderRadioField(Component\Input\Field\Radio $input, RendererInterface $default_renderer)
    {
        $input_tpl = $this->getTemplate("tpl.radio.html", true, true);

        //monitor change-events
        $input = $input->withAdditionalOnLoadCode(function ($id) {
            return "il.UI.Input.radio.init('$id');";
        });
        $id = $this->bindJavaScript($input);
        $input_tpl->setVariable("ID", $id);

        foreach ($input->getOptions() as $value => $label) {
            $group_id = $id . '_' . $value . '_group';
            $opt_id = $id . '_' . $value . '_opt';

            $input_tpl->setCurrentBlock('optionblock');
            $input_tpl->setVariable("NAME", $input->getName());
            $input_tpl->setVariable("OPTIONID", $opt_id);
            $input_tpl->setVariable("VALUE", $value);
            $input_tpl->setVariable("LABEL", $label);

            if ($input->getValue() !== null && $input->getValue() === $value) {
                $input_tpl->setVariable("CHECKED", 'checked="checked"');
            }

            $byline = $input->getBylineFor($value);
            if (!empty($byline)) {
                $input_tpl->setVariable("BYLINE", $byline);
            }

            //dependant fields
            $dependant_group_html = '';
            $dep_fields = $input->getDependantFieldsFor($value);
            if (!is_null($dep_fields)) {
                $inputs_html = '';
                $dependant_group_tpl = $this->getTemplate("tpl.dependant_group.html", true, true);
                foreach ($dep_fields as $key => $inpt) {
                    $inputs_html .= $default_renderer->render($inpt);
                }
                $dependant_group_tpl->setVariable("CONTENT", $inputs_html);
                $dependant_group_tpl->setVariable("ID", $group_id);
                $dependant_group_html = $dependant_group_tpl->get();
            }
            $input_tpl->setVariable("DEPENDANT_FIELDS", $dependant_group_html);

            $input_tpl->parseCurrentBlock();
        }
        $options_html = $input_tpl->get();

        //render with context:
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);
        $tpl->setVariable("LABEL", $input->getLabel());
        $tpl->setVariable("INPUT", $options_html);

        if ($input->getByline() !== null) {
            $tpl->setCurrentBlock("byline");
            $tpl->setVariable("BYLINE", $input->getByline());
            $tpl->parseCurrentBlock();
        }
        if ($input->isRequired()) {
            $tpl->touchBlock("required");
        }
        if ($input->getError() !== null && $input->getError() != $input::DEPENDANT_FIELD_ERROR) {
            $tpl->setCurrentBlock("error");
            $tpl->setVariable("ERROR", $input->getError());
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return [
            Component\Input\Field\Text::class,
            Component\Input\Field\Numeric::class,
            Component\Input\Field\Group::class,
            Component\Input\Field\Section::class,
            Component\Input\Field\Checkbox::class,
            Component\Input\Field\Tag::class,
            Component\Input\Field\DependantGroup::class,
            Component\Input\Field\Password::class,
            Component\Input\Field\Select::class,
            Component\Input\Field\Radio::class,
            Component\Input\Field\Textarea::class,
            Component\Input\Field\MultiSelect::class
        ];
    }
}
