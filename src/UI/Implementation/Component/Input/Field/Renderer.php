<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\DateFormat as DateFormat;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Input\Field as F;
use ILIAS\UI\Component\Input\Field as FI;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Renderer extends AbstractComponentRenderer
{
    public const DATEPICKER_MINMAX_FORMAT = 'Y/m/d';

    public const DATEPICKER_FORMAT_MAPPING = [
        'd' => 'DD',
        'jS' => 'Do',
        'l' => 'dddd',
        'D' => 'dd',
        'S' => 'o',
        'W' => '',
        'm' => 'MM',
        'F' => 'MMMM',
        'M' => 'MMM',
        'Y' => 'YYYY',
        'y' => 'YY'
    ];

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var $component Input
         */
        $this->checkComponent($component);

        $component = $this->setSignals($component);

        switch (true) {
            case ($component instanceof F\OptionalGroup):
                return $this->renderOptionalGroup($component, $default_renderer);

            case ($component instanceof F\SwitchableGroup):
                return $this->renderSwitchableGroup($component, $default_renderer);

            case ($component instanceof F\Section):
                return $this->renderSection($component, $default_renderer);

            case ($component instanceof F\Duration):
                return $this->renderDurationField($component, $default_renderer);

            case ($component instanceof F\Group):
                return $default_renderer->render($component->getInputs());

            case ($component instanceof F\Text):
                return $this->renderTextField($component);

            case ($component instanceof F\Numeric):
                return $this->renderNumericField($component);

            case ($component instanceof F\Checkbox):
                return $this->renderCheckboxField($component);

            case ($component instanceof F\Tag):
                return $this->renderTagField($component);

            case ($component instanceof F\Password):
                return $this->renderPasswordField($component, $default_renderer);

            case ($component instanceof F\Select):
                return $this->renderSelectField($component);

            case ($component instanceof F\Textarea):
                return $this->renderTextareaField($component);

            case ($component instanceof F\Radio):
                return $this->renderRadioField($component);

            case ($component instanceof F\MultiSelect):
                return $this->renderMultiSelectField($component);

            case ($component instanceof F\DateTime):
                return $this->renderDateTimeField($component, $default_renderer);

            case ($component instanceof F\File):
                return $this->renderFileField($component, $default_renderer);

            default:
                throw new \LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    protected function wrapInFormContext(
        FI\FormInput $component,
        string $input_html,
        string $id_pointing_to_input = '',
        string $dependant_group_html = ''
    ) : string {
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);

        $tpl->setVariable("INPUT", $input_html);

        if ($id_pointing_to_input) {
            $tpl->setCurrentBlock('for');
            $tpl->setVariable("ID", $id_pointing_to_input);
            $tpl->parseCurrentBlock();
        }

        $label = $component->getLabel();
        $tpl->setVariable("LABEL", $label);

        $byline = $component->getByline();
        if ($byline) {
            $tpl->setVariable("BYLINE", $byline);
        }

        $required = $component->isRequired();
        if ($required) {
            $tpl->touchBlock("required");
        }

        $error = $component->getError();
        if ($error) {
            $tpl->setVariable("ERROR", $error);
        }

        $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        return $tpl->get();
    }

    protected function maybeDisable(FI\FormInput $component, Template $tpl) : void
    {
        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", 'disabled="disabled"');
        }
    }

    protected function applyName(FI\FormInput $component, Template $tpl) : ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function bindJSandApplyId(FI\FormInput $component, Template $tpl) : string
    {
        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable("ID", $id);
        return $id;
    }

    /**
     * Escape values for rendering with a Callable "$escape"
     * In order to prevent XSS-attacks, values need to be stripped of
     * special chars (such as quotes or tags).
     * Needs vary according to the type of component, i.e.the html generated
     * for this specific component and the placement of {VALUE} in its template.
     * Please note: this may not work for customized templates!
     */
    protected function applyValue(FI\FormInput $component, Template $tpl, callable $escape = null)
    {
        $value = $component->getValue();
        if (!is_null($escape)) {
            $value = $escape($value);
        }
        if (isset($value) && strlen($value) > 0) {
            $tpl->setVariable("VALUE", $value);
        }
    }

    protected function escapeSpecialChars() : \Closure
    {
        return function ($v) {
            return htmlspecialchars($v, ENT_QUOTES);
        };
    }

    protected function renderTextField(F\Text $component) : string
    {
        $tpl = $this->getTemplate("tpl.text.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderNumericField(F\Numeric $component) : string
    {
        $tpl = $this->getTemplate("tpl.numeric.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderCheckboxField(F\Checkbox $component) : string
    {
        $tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }

        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderOptionalGroup(F\OptionalGroup $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }
        /**
         * @var $component F\OptionalGroup
         */
        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "il.UI.Input.groups.optional.init('{$id}')";
        });
        $id = $this->bindJSandApplyId($component, $tpl);

        $dependant_group_html = $default_renderer->render($component->getInputs());

        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id, $dependant_group_html);
    }

    protected function renderSwitchableGroup(F\SwitchableGroup $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.radio.html", true, true);

        /**
         * @var $component F\SwitchableGroup
         */
        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "il.UI.Input.groups.switchable.init('{$id}')";
        });
        $id = $this->bindJSandApplyId($component, $tpl);

        foreach ($component->getInputs() as $key => $group) {
            $opt_id = $id . '_' . $key . '_opt';

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable("NAME", $component->getName());
            $tpl->setVariable("OPTIONID", $opt_id);
            $tpl->setVariable("VALUE", $key);
            $tpl->setVariable("LABEL", $group->getLabel());

            if ($component->getValue() !== null) {
                list($index, $subvalues) = $component->getValue();
                if ($index == $key) {
                    $tpl->setVariable("CHECKED", 'checked="checked"');
                }
            }

            $dependant_group_html = $default_renderer->render($group);
            $tpl->setVariable("DEPENDANT_FIELDS", $dependant_group_html);
            $tpl->parseCurrentBlock();

            if ($component->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }
        }

        return $this->wrapInFormContext($component, $tpl->get());
    }

    protected function renderTagField(F\Tag $component) : string
    {
        $tpl = $this->getTemplate("tpl.tag_input.html", true, true);
        $this->applyName($component, $tpl);

        $configuration = $component->getConfiguration();
        $value = $component->getValue();
        
        if ($value) {
            $value = array_map(
                function ($v) {
                    return ['value' => urlencode($v), 'display' => $v];
                },
                $value
            );
        }

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($configuration, $value) {
                $encoded = json_encode($configuration);
                $value = json_encode($value);
                return "il.UI.Input.tagInput.init('{$id}', {$encoded}, {$value});";
            }
        );
        $id = $this->bindJSandApplyId($component, $tpl);

        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", "disabled");
            $tpl->setVariable("READONLY", "readonly");
        }

        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderPasswordField(F\Password $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.password.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getRevelation()) {
            $component = $component->withResetSignals();
            $sig_reveal = $component->getRevealSignal();
            $sig_mask = $component->getMaskSignal();
            $component = $component->withAdditionalOnLoadCode(function ($id) use ($sig_reveal, $sig_mask) {
                return
                    "$(document).on('{$sig_reveal}', function() {
                        $('#{$id}').addClass('revealed');
                        $('#{$id}')[0].getElementsByTagName('input')[0].type='text';
                    });" .
                    "$(document).on('{$sig_mask}', function() {
                        $('#{$id}').removeClass('revealed');
                        $('#{$id}')[0].getElementsByTagName('input')[0].type='password';
                    });";
            });

            $f = $this->getUIFactory();
            $glyph_reveal = $f->symbol()->glyph()->eyeopen("#")
                              ->withOnClick($sig_reveal);
            $glyph_mask = $f->symbol()->glyph()->eyeclosed("#")
                            ->withOnClick($sig_mask);

            $tpl->setVariable('PASSWORD_REVEAL', $default_renderer->render($glyph_reveal));
            $tpl->setVariable('PASSWORD_MASK', $default_renderer->render($glyph_mask));
        }
        $id = $this->bindJSandApplyId($component, $tpl);

        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get());
    }

    public function renderSelectField(F\Select $component) : string
    {
        $tpl = $this->getTemplate("tpl.select.html", true, true);
        $this->applyName($component, $tpl);

        $value = $component->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", 'selected="selected"');
        }
        if ($component->isRequired()) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }
        $tpl->setVariable("VALUE", null);
        $tpl->setVariable("VALUE_STR", "-");
        $tpl->parseCurrentBlock();

        foreach ($component->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if ($value == $option_key) {
                $tpl->setVariable("SELECTED", 'selected="selected"');
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderTextareaField(F\Textarea $component) : string
    {
        $tpl = $this->getTemplate("tpl.textarea.html", true, true);
        $this->applyName($component, $tpl);

        $id = "";

        if ($component->isLimited()) {
            $this->toJS("ui_chars_remaining");
            $this->toJS("ui_chars_min");
            $this->toJS("ui_chars_max");

            $counter_id_prefix = "textarea_feedback_";
            $min = $component->getMinLimit();
            $max = $component->getMaxLimit();

            /**
             * @var $component F\Textarea
             */
            $component = $component->withAdditionalOnLoadCode(function ($id) use ($counter_id_prefix, $min, $max) {
                return "il.UI.textarea.changeCounter('$id','$counter_id_prefix','$min','$max');";
            });

            $id = $this->bindJSandApplyId($component, $tpl);

            $tpl->setVariable("COUNT_ID", $id);
            $tpl->setVariable("FEEDBACK_MAX_LIMIT", $max);
        } else {
            $id = $this->bindJSandApplyId($component, $tpl);
        }

        $this->applyValue($component, $tpl, 'htmlentities');
        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderRadioField(F\Radio $component) : string
    {
        $tpl = $this->getTemplate("tpl.radio.html", true, true);
        $id = $this->bindJSandApplyId($component, $tpl);

        foreach ($component->getOptions() as $value => $label) {
            $opt_id = $id . '_' . $value . '_opt';

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable("NAME", $component->getName());
            $tpl->setVariable("OPTIONID", $opt_id);
            $tpl->setVariable("VALUE", $value);
            $tpl->setVariable("LABEL", $label);

            if ($component->getValue() !== null && $component->getValue() == $value) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }
            if ($component->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }

            $byline = $component->getBylineFor($value);
            if (!empty($byline)) {
                $tpl->setVariable("BYLINE", $byline);
            }

            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFormContext($component, $tpl->get());
    }

    protected function renderMultiSelectField(F\MultiSelect $component) : string
    {
        $tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        $name = $this->applyName($component, $tpl);

        $value = $component->getValue();
        $tpl->setVariable("VALUE", $value);

        $id = $this->bindJSandApplyId($component, $tpl);
        $tpl->setVariable("ID", $id);

        foreach ($component->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("NAME", $name);
            $tpl->setVariable("VALUE", $opt_value);
            $tpl->setVariable("LABEL", $opt_label);

            if ($value && in_array($opt_value, $value)) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }

            if ($component->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }

            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFormContext($component, $tpl->get());
    }

    protected function renderDateTimeField(F\DateTime $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.datetime.html", true, true);
        $this->applyName($component, $tpl);

        $f = $this->getUIFactory();

        if ($component->getTimeOnly() === true) {
            $cal_glyph = $f->symbol()->glyph()->time("#");
            $format = $component::TIME_FORMAT;
        } else {
            $cal_glyph = $f->symbol()->glyph()->calendar("#");

            $format = $this->getTransformedDateFormat(
                $component->getFormat(),
                self::DATEPICKER_FORMAT_MAPPING
            );

            if ($component->getUseTime() === true) {
                $format .= ' ' . $component::TIME_FORMAT;
            }
        }
        $tpl->setVariable("CALENDAR_GLYPH", $default_renderer->render($cal_glyph));

        $config = [
            'showClear' => true,
            'sideBySide' => true,
            'format' => $format,
            'locale' => $this->getLangKey()
        ];
        $config = array_merge($config, $component->getAdditionalPickerconfig());

        $min_date = $component->getMinValue();
        if (!is_null($min_date)) {
            $config['minDate'] = date_format($min_date, self::DATEPICKER_MINMAX_FORMAT);
        }
        $max_date = $component->getMaxValue();
        if (!is_null($max_date)) {
            $config['maxDate'] = date_format($max_date, self::DATEPICKER_MINMAX_FORMAT);
        }

        $tpl->setVariable("PLACEHOLDER", $format);

        if ($component->getValue() !== null) {
            $tpl->setVariable("VALUE", $component->getValue());
        }

        $disabled = $component->isDisabled();

        /**
         * @var $component F\DateTime
         */
        $component = $component->withAdditionalOnLoadCode(function ($id) use ($config, $disabled) {
            $js = '$("#' . $id . '").datetimepicker(' . json_encode($config) . ');';
            if ($disabled) {
                $js .= '$("#' . $id . ' input").prop(\'disabled\', true);';
            }
            return $js;
        });

        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderDurationField(F\Duration $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.duration.html", true, true);
        $this->applyName($component, $tpl);

        /**
         * @var $component F\Duration
         */
        $component = $component->withAdditionalOnLoadCode(
            function ($id) {
                return "$(document).ready(function() {
                    il.UI.Input.duration.init('$id');
                });";
            }
        );
        $id = $this->bindJSandApplyId($component, $tpl);

        $input_html = '';
        $inputs = $component->getInputs();
        $input = array_shift($inputs); //from
        $input_html .= $default_renderer->render($input);
        $input = array_shift($inputs)->withAdditionalPickerconfig([ //until
                                                                    'useCurrent' => false
        ]);
        $input_html .= $default_renderer->render($input);
        $tpl->setVariable('DURATION', $input_html);

        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderFileField(F\File $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.file.html", true, true);
        $this->applyName($component, $tpl);

        $settings = new \stdClass();
        $settings->upload_url = $component->getUploadHandler()->getUploadURL();
        $settings->removal_url = $component->getUploadHandler()->getFileRemovalURL();
        $settings->info_url = $component->getUploadHandler()->getExistingFileInfoURL();
        $settings->file_identifier_key = $component->getUploadHandler()->getFileIdentifierParameterName();
        $settings->accepted_files = implode(',', $component->getAcceptedMimeTypes());
        $settings->existing_file_ids = $component->getValue();
        $settings->existing_files = $component->getUploadHandler()->getInfoForExistingFiles($component->getValue() ?? []);
        $upload_limit = \ilUtil::getUploadSizeLimitBytes();
        $max_file_size = $component->getMaxFileFize() === -1
            ? $upload_limit
            : $component->getMaxFileFize();
        // dropzone.js expects MiB, latest documentation is misleading, see
        // https://github.com/dropzone/dropzone/issues/2197
        $settings->max_file_size = min($max_file_size, $upload_limit) / 1024 / 1024;
        $settings->max_file_size_text = sprintf(
            $this->txt('ui_file_input_invalid_size'),
            (string) round($settings->max_file_size, 3)
        );

        /**
         * @var $component F\File
         */
        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($settings) {
                $settings = json_encode($settings);
                return "$(document).ready(function() {
                    il.UI.Input.file.init('$id', '{$settings}');
                });";
            }
        );
        $id = $this->bindJSandApplyId($component, $tpl);

        $tpl->setVariable(
            'BUTTON',
            $default_renderer->render(
                $this->getUIFactory()->button()->shy(
                    $this->txt('select_files_from_computer'),
                    "#"
                )
            )
        );

        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function renderSection(F\Section $section, RendererInterface $default_renderer) : string
    {
        $section_tpl = $this->getTemplate("tpl.section.html", true, true);
        $inputs_html = "";
        foreach ($section->getInputs() as $input) {
            $inputs_html .= $default_renderer->render($input);
        }

        $section_tpl->setVariable("INPUTS", $inputs_html);
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

        return $section_tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./node_modules/moment/min/moment-with-locales.min.js');
        $registry->register('./node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
        
        $registry->register('./node_modules/@yaireo/tagify/dist/tagify.min.js');
        $registry->register('./node_modules/@yaireo/tagify/dist/tagify.css');
        $registry->register('./src/UI/templates/js/Input/Field/tagInput.js');

        $registry->register('./src/UI/templates/js/Input/Field/textarea.js');
        $registry->register('./src/UI/templates/js/Input/Field/input.js');
        $registry->register('./src/UI/templates/js/Input/Field/duration.js');
        $registry->register('./node_modules/dropzone/dist/min/dropzone.min.js');
        $registry->register('./src/UI/templates/js/Input/Field/file.js');
        $registry->register('./src/UI/templates/js/Input/Field/groups.js');
    }

    /**
     * @param Input $input
     * @return Input|\ILIAS\UI\Implementation\Component\JavaScriptBindable
     */
    protected function setSignals(Input $input)
    {
        $signals = null;
        foreach ($input->getTriggeredSignals() as $s) {
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }
        if ($signals !== null) {
            $signals = json_encode($signals);

            /**
             * @var $input Input
             */
            $input = $input->withAdditionalOnLoadCode(function ($id) use ($signals) {
                $code = "il.UI.input.setSignalsForId('$id', $signals);";
                return $code;
            });

            $input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
        }
        return $input;
    }

    /**
     * Return the datetime format in a form fit for the JS-component of this input.
     * Currently, this means transforming the elements of DateFormat to momentjs.
     * http://eonasdan.github.io/bootstrap-datetimepicker/Options/#format
     * http://momentjs.com/docs/#/displaying/format/
     */
    protected function getTransformedDateFormat(
        DateFormat\DateFormat $origin,
        array $mapping
    ) : string {
        $ret = '';
        foreach ($origin->toArray() as $element) {
            if (array_key_exists($element, $mapping)) {
                $ret .= $mapping[$element];
            } else {
                $ret .= $element;
            }
        }
        return $ret;
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
            Component\Input\Field\OptionalGroup::class,
            Component\Input\Field\SwitchableGroup::class,
            Component\Input\Field\Section::class,
            Component\Input\Field\Checkbox::class,
            Component\Input\Field\Tag::class,
            Component\Input\Field\Password::class,
            Component\Input\Field\Select::class,
            Component\Input\Field\Radio::class,
            Component\Input\Field\Textarea::class,
            Component\Input\Field\MultiSelect::class,
            Component\Input\Field\DateTime::class,
            Component\Input\Field\Duration::class,
            Component\Input\Field\File::class
        ];
    }

    protected function renderFileInput(F\File $input) : Component\Input\Field\File
    {
        $component = $this->setSignals($input);
        /**
         * @var $component File
         */
        $settings = new \stdClass();
        $settings->upload_url = $component->getUploadHandler()->getUploadURL();
        $settings->removal_url = $component->getUploadHandler()->getFileRemovalURL();
        $settings->info_url = $component->getUploadHandler()->getExistingFileInfoURL();
        $settings->file_identifier_key = $component->getUploadHandler()->getFileIdentifierParameterName();
        $settings->accepted_files = implode(',', $component->getAcceptedMimeTypes());
        $settings->existing_file_ids = $input->getValue();
        $settings->existing_files = $component->getUploadHandler()->getInfoForExistingFiles($input->getValue() ?? []);
        $settings->dictInvalidFileType = $this->txt('form_msg_file_wrong_file_type');

        $input = $component->withAdditionalOnLoadCode(
            function ($id) use ($settings) {
                $settings = json_encode($settings);

                return "$(document).ready(function() {
					il.UI.Input.file.init('$id', '{$settings}');
				});";
            }
        );

        /**
         * @var $input Component\Input\Field\File
         */
        return $input;
    }
}
