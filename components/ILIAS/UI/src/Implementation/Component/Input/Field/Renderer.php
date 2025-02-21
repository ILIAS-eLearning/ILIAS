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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\DateFormat;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Input\Field as F;
use ILIAS\UI\Component\Input\Field as FI;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\Template;
use LogicException;
use Closure;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\Data\DataSize;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\Data\FiveStarRatingScale;
use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Renderer extends AbstractComponentRenderer
{
    public const DATETIME_DATEPICKER_MINMAX_FORMAT = 'Y-m-d\Th:m';
    public const DATE_DATEPICKER_MINMAX_FORMAT = 'Y-m-d';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime-local';
    public const TYPE_TIME = 'time';
    public const HTML5_NATIVE_DATETIME_FORMAT = 'Y-m-d H:i';
    public const HTML5_NATIVE_DATE_FORMAT = 'Y-m-d';
    public const HTML5_NATIVE_TIME_FORMAT = 'H:i';

    public const DATEPICKER_FORMAT_MAPPING = [
        'd' => 'DD',
        'jS' => 'Do',
        'l' => 'dddd',
        'D' => 'dd',
        'S' => 'o',
        'i' => 'mm',
        'W' => '',
        'm' => 'MM',
        'F' => 'MMMM',
        'M' => 'MMM',
        'Y' => 'YYYY',
        'y' => 'YY'
    ];

    /**
     * @var float This factor will be used to calculate a percentage of the PHP upload-size limit which
     *            will be used as chunk-size for chunked uploads. This needs to be done because file uploads
     *            fail if the file is exactly as big as this limit or slightly less. 90% turned out to be a
     *            functional fraction for now.
     */
    protected const FILE_UPLOAD_CHUNK_SIZE_FACTOR = 0.9;

    private const CENTUM = 100;

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
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

            case ($component instanceof F\Link):
                return $this->renderLinkField($component, $default_renderer);

            case ($component instanceof F\Group):
                return $default_renderer->render($component->getInputs());

            case ($component instanceof F\Text):
                return $this->renderTextField($component, $default_renderer);

            case ($component instanceof F\Numeric):
                return $this->renderNumericField($component, $default_renderer);

            case ($component instanceof F\Checkbox):
                return $this->renderCheckboxField($component, $default_renderer);

            case ($component instanceof F\Tag):
                return $this->renderTagField($component, $default_renderer);

            case ($component instanceof F\Password):
                return $this->renderPasswordField($component, $default_renderer);

            case ($component instanceof F\Select):
                return $this->renderSelectField($component, $default_renderer);

            case ($component instanceof F\Markdown):
                return $this->renderMarkdownField($component, $default_renderer);

            case ($component instanceof F\Textarea):
                return $this->renderTextareaField($component, $default_renderer);

            case ($component instanceof F\Radio):
                return $this->renderRadioField($component, $default_renderer);

            case ($component instanceof F\MultiSelect):
                return $this->renderMultiSelectField($component, $default_renderer);

            case ($component instanceof F\DateTime):
                return $this->renderDateTimeField($component, $default_renderer);

            case ($component instanceof F\File):
                return $this->renderFileField($component, $default_renderer);

            case ($component instanceof F\Url):
                return $this->renderUrlField($component, $default_renderer);

            case ($component instanceof F\Hidden):
                return $this->renderHiddenField($component);

            case ($component instanceof F\ColorPicker):
                return $this->renderColorPickerField($component, $default_renderer);

            case ($component instanceof F\Rating):
                return $this->renderRatingField($component, $default_renderer);

            default:
                $this->cannotHandleComponent($component);
        }
    }

    protected function wrapInFormContext(
        FormInput $component,
        string $label,
        string $input_html,
        ?string $id_for_label = null,
        ?string $dependant_group_html = null
    ): string {
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);

        $tpl->setVariable("LABEL", $label);
        $tpl->setVariable("INPUT", $input_html);
        $tpl->setVariable("UI_COMPONENT_NAME", $this->getComponentCanonicalNameAttribute($component));
        $tpl->setVariable("INPUT_NAME", $component->getName());

        if ($component->getOnLoadCode() !== null) {
            $binding_id = $this->bindJavaScript($component) ?? $this->createId();
            $tpl->setVariable("BINDING_ID", $binding_id);
        }

        if ($id_for_label) {
            $tpl->setCurrentBlock('for');
            $tpl->setVariable("ID", $id_for_label);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock('tabindex');
        }

        $byline = $component->getByline();
        if ($byline) {
            $tpl->setVariable("BYLINE", $byline);
        }

        $required = $component->isRequired();
        if ($required) {
            $tpl->setCurrentBlock('required');
            $tpl->setVariable("REQUIRED_ARIA", $this->txt('required_field'));
            $tpl->parseCurrentBlock();
        }

        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled");
        }

        $error = $component->getError();
        if ($error) {
            $error_id = $this->createId();
            $tpl->setVariable("ERROR_LABEL", $this->txt("ui_error"));
            $tpl->setVariable("ERROR_ID", $error_id);
            $tpl->setVariable("ERROR", $error);
            if ($id_for_label) {
                $tpl->setVariable("ERROR_FOR_ID", $id_for_label);
            }
        }

        if ($dependant_group_html) {
            $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        }
        return $tpl->get();
    }

    protected function applyName(FormInput $component, Template $tpl): ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function bindJSandApplyId(Component\JavaScriptBindable $component, Template $tpl): string
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
    protected function applyValue(FormInput $component, Template $tpl, callable $escape = null): void
    {
        $value = $component->getValue();
        if (!is_null($escape)) {
            $value = $escape($value);
        }
        if (isset($value) && $value !== '') {
            $tpl->setVariable("VALUE", $value);
        }
    }

    protected function escapeSpecialChars(): Closure
    {
        return function ($v) {
            // with declare(strict_types=1) in place,
            // htmlspecialchars will not silently convert to string anymore;
            // therefore, the typecast must be explicit
            return htmlspecialchars((string) $v, ENT_QUOTES);
        };
    }

    protected function htmlEntities(): Closure
    {
        return function ($v) {
            // with declare(strict_types=1) in place,
            // htmlentities will not silently convert to string anymore;
            // therefore, the typecast must be explicit
            return htmlentities((string) $v);
        };
    }

    protected function renderLinkField(F\Link $component, RendererInterface $default_renderer): string
    {
        $input_html = $default_renderer->render($component->getInputs());
        return $this->wrapInFormContext(
            $component,
            $component->getLabel(),
            $input_html,
        );
    }

    protected function renderTextField(F\Text $component): string
    {
        $tpl = $this->getTemplate("tpl.text.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getMaxLength()) {
            $tpl->setVariable("MAX_LENGTH", $component->getMaxLength());
        }

        $this->applyValue($component, $tpl, $this->escapeSpecialChars());

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderNumericField(F\Numeric $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.numeric.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderCheckboxField(F\Checkbox $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderOptionalGroup(F\OptionalGroup $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.optionalgroup_label.html", true, true);
        $tpl->setVariable('LABEL', $component->getLabel());
        $tpl->setVariable("NAME", $component->getName());
        if ($component->getValue()) {
            $tpl->setVariable("CHECKED", 'checked="checked"');
        }

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);

        $label = $tpl->get();
        $input_html = $default_renderer->render($component->getInputs());

        return $this->wrapInFormContext($component, $label, $input_html, $label_id);
    }

    protected function renderSwitchableGroup(F\SwitchableGroup $component, RendererInterface $default_renderer): string
    {
        $value = null;
        if ($component->getValue() !== null) {
            list($value, ) = $component->getValue();
        }

        $input_html = '';
        foreach ($component->getInputs() as $key => $group) {
            $tpl = $this->getTemplate("tpl.switchablegroup_label.html", true, true);
            $tpl->setVariable('LABEL', $group->getLabel());
            $tpl->setVariable("NAME", $component->getName());
            $tpl->setVariable("VALUE", $key);

            $label_id = $this->createId();
            $tpl->setVariable('ID', $label_id);

            if ($key == $value) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }

            $input_html .= $this->wrapInFormContext(
                $group,
                $tpl->get(),
                $default_renderer->render($group),
                $label_id
            );
        }


        return $this->wrapInFormContext(
            $component,
            $component->getLabel(),
            $input_html
        );
    }

    protected function renderTagField(F\Tag $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.tag_input.html", true, true);
        $this->applyName($component, $tpl);

        $configuration = $component->getConfiguration();
        $value = $component->getValue();

        if ($value) {
            $value = array_map(
                function ($v) {
                    return ['value' => urlencode($this->convertSpecialCharacters($v)), 'display' => $v];
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

        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", "disabled");
            $tpl->setVariable("READONLY", "readonly");
        }

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderPasswordField(F\Password $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.password.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getRevelation()) {
            $component = $component->withResetSignals();
            $sig_reveal = $component->getRevealSignal();
            $sig_mask = $component->getMaskSignal();
            $component = $component->withAdditionalOnLoadCode(function ($id) use ($sig_reveal, $sig_mask) {
                return
                    "$(document).on('$sig_reveal', function() {
                        const fieldContainer = document.querySelector('#$id .c-input__field .c-field-password');
                        fieldContainer.classList.add('revealed');
                        fieldContainer.getElementsByTagName('input').item(0).type='text';
                    });" .
                    "$(document).on('$sig_mask', function() {
                        const fieldContainer = document.querySelector('#$id .c-input__field .c-field-password');
                        fieldContainer.classList.remove('revealed');
                        fieldContainer.getElementsByTagName('input').item(0).type='password';
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

        $this->applyValue($component, $tpl, $this->escapeSpecialChars());

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    public function renderSelectField(F\Select $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.select.html", true, true);
        $this->applyName($component, $tpl);

        $value = $component->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", 'selected="selected"');
        }
        if ($component->isRequired() && !$value) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }

        if (!($value && $component->isRequired())) {
            $tpl->setVariable("VALUE", null);
            $tpl->setVariable("VALUE_STR", $component->isRequired() ? $this->txt('ui_select_dropdown_label') : '-');
            $tpl->parseCurrentBlock();
        }

        foreach ($component->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if ($value == $option_key) {
                $tpl->setVariable("SELECTED", 'selected="selected"');
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderMarkdownField(F\Markdown $component, RendererInterface $default_renderer): string
    {
        /** @var $component F\Markdown */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id) use ($component): string {
                return "
                    const id = document.querySelector('#$id .c-input__field textarea')?.id;
                    il.UI.Input.markdown.init(
                        id,
                        '{$component->getMarkdownRenderer()->getAsyncUrl()}',
                        '{$component->getMarkdownRenderer()->getParameterName()}'
                    );
                ";
            }
        );

        $textarea_id = $this->createId();
        $textarea_tpl = $this->getPreparedTextareaTemplate($component);
        $textarea_tpl->setVariable('ID', $textarea_id);

        $markdown_tpl = $this->getTemplate("tpl.markdown.html", true, true);
        $markdown_tpl->setVariable('TEXTAREA', $textarea_tpl->get());

        $markdown_tpl->setVariable(
            'PREVIEW',
            $component->getMarkdownRenderer()->render(
                $this->htmlEntities()($component->getValue() ?? '')
            )
        );

        $markdown_tpl->setVariable(
            'VIEW_CONTROLS',
            $default_renderer->render(
                $this->getUIFactory()->viewControl()->mode([
                    $this->txt('ui_md_input_edit') => '#',
                    $this->txt('ui_md_input_view') => '#',
                ], "")
            )
        );

        /** @var $markdown_actions_glyphs Component\Symbol\Glyph\Glyph[] */
        $markdown_actions_glyphs = [
            'ACTION_HEADING' => $this->getUIFactory()->symbol()->glyph()->header(),
            'ACTION_LINK' => $this->getUIFactory()->symbol()->glyph()->link(),
            'ACTION_BOLD' => $this->getUIFactory()->symbol()->glyph()->bold(),
            'ACTION_ITALIC' => $this->getUIFactory()->symbol()->glyph()->italic(),
            'ACTION_ORDERED_LIST' => $this->getUIFactory()->symbol()->glyph()->numberedlist(),
            'ACTION_UNORDERED_LIST' => $this->getUIFactory()->symbol()->glyph()->bulletlist()
        ];

        foreach ($markdown_actions_glyphs as $tpl_variable => $glyph) {
            if ($component->isDisabled()) {
                $glyph = $glyph->withUnavailableAction();
            }

            $action = $this->getUIFactory()->button()->standard('', '#')->withSymbol($glyph);

            if ($component->isDisabled()) {
                $action = $action->withUnavailableAction();
            }

            $markdown_tpl->setVariable($tpl_variable, $default_renderer->render($action));
        }

        return $this->wrapInFormContext($component, $component->getLabel(), $markdown_tpl->get());
    }

    protected function renderTextareaField(F\Textarea $component, RendererInterface $default_renderer): string
    {
        /** @var $component F\Textarea */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id): string {
                return "
                    taId = document.querySelector('#$id .c-input__field textarea')?.id;
                    il.UI.Input.textarea.init(taId);
                ";
            }
        );

        $tpl = $this->getPreparedTextareaTemplate($component);

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function getPreparedTextareaTemplate(F\Textarea $component): Template
    {
        $tpl = $this->getTemplate("tpl.textarea.html", true, true);

        if (0 < $component->getMaxLimit()) {
            $tpl->setVariable('REMAINDER_TEXT', $this->txt('ui_chars_remaining'));
            $tpl->setVariable('REMAINDER', $component->getMaxLimit() - strlen($component->getValue() ?? ''));
            $tpl->setVariable('MAX_LIMIT', $component->getMaxLimit());
        }

        if (null !== $component->getMinLimit()) {
            $tpl->setVariable('MIN_LIMIT', $component->getMinLimit());
        }

        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->htmlEntities());
        return $tpl;
    }

    protected function renderRadioField(F\Radio $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.radio.html", true, true);
        $id = $this->createId();

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

            $byline = $component->getBylineFor((string) $value);
            if (!empty($byline)) {
                $tpl->setVariable("BYLINE", $byline);
            }

            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());
    }

    protected function renderMultiSelectField(F\MultiSelect $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.multiselect.html", true, true);

        $options = $component->getOptions();
        if (count($options) > 0) {
            $value = $component->getValue();
            $name = $this->applyName($component, $tpl);
            foreach ($options as $opt_value => $opt_label) {
                $tpl->setCurrentBlock("option");
                $tpl->setVariable("NAME", $name);
                $tpl->setVariable("VALUE", $opt_value);
                $tpl->setVariable("LABEL", $opt_label);

                if ($value && in_array($opt_value, $value)) {
                    $tpl->setVariable("CHECKED", 'checked="checked"');
                }
                $tpl->parseCurrentBlock();
            }
        } else {
            $tpl->touchBlock("no_options");
        }

        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());
    }

    protected function renderDateTimeField(F\DateTime $component, RendererInterface $default_renderer): string
    {
        list($component, $tpl) = $this->internalRenderDateTimeField($component, $default_renderer);
        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    /**
     * @return array<DateTime,Template>
     */
    protected function internalRenderDateTimeField(F\DateTime $component, RendererInterface $default_renderer): array
    {
        $tpl = $this->getTemplate("tpl.datetime.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getTimeOnly() === true) {
            $format = $component::TIME_FORMAT;
            $dt_type = self::TYPE_TIME;
        } else {
            $dt_type = self::TYPE_DATE;
            $format = $this->getTransformedDateFormat(
                $component->getFormat(),
                self::DATEPICKER_FORMAT_MAPPING
            );

            if ($component->getUseTime() === true) {
                $format .= ' ' . $component::TIME_FORMAT;
                $dt_type = self::TYPE_DATETIME;
            }
        }

        $tpl->setVariable("DTTYPE", $dt_type);

        $min_max_format = self::DATE_DATEPICKER_MINMAX_FORMAT;
        if ($dt_type === self::TYPE_DATETIME) {
            $min_max_format = self::DATETIME_DATEPICKER_MINMAX_FORMAT;
        }

        $min_date = $component->getMinValue();
        if (!is_null($min_date)) {
            $tpl->setVariable("MIN_DATE", date_format($min_date, $min_max_format));
        }
        $max_date = $component->getMaxValue();
        if (!is_null($max_date)) {
            $tpl->setVariable("MAX_DATE", date_format($max_date, $min_max_format));
        }

        $this->applyValue($component, $tpl, function (?string $value) use ($dt_type) {
            if ($value !== null) {
                $value = new \DateTimeImmutable($value);
                return $value->format(match ($dt_type) {
                    self::TYPE_DATETIME => self::HTML5_NATIVE_DATETIME_FORMAT,
                    self::TYPE_DATE => self::HTML5_NATIVE_DATE_FORMAT,
                    self::TYPE_TIME => self::HTML5_NATIVE_TIME_FORMAT,
                });
            }
            return null;
        });
        return [$component, $tpl];
    }

    protected function renderDurationField(F\Duration $component, RendererInterface $default_renderer): string
    {
        $inputs = $component->getInputs();

        $input = array_shift($inputs); //from
        list($input, $tpl) = $this->internalRenderDateTimeField($input, $default_renderer);

        $from_input_id = $this->createId();
        $tpl->setVariable('ID', $from_input_id);
        $input_html = $this->wrapInFormContext($input, $input->getLabel(), $tpl->get(), $from_input_id);

        $input = array_shift($inputs) //until
            ->withAdditionalPickerconfig(['useCurrent' => false]);
        list($input, $tpl) = $this->internalRenderDateTimeField($input, $default_renderer);
        $until_input_id = $this->createId();
        $tpl->setVariable('ID', $until_input_id);
        $input_html .= $this->wrapInFormContext($input, $input->getLabel(), $tpl->get(), $until_input_id);

        $tpl = $this->getTemplate("tpl.duration.html", true, true);
        $tpl->setVariable('DURATION', $input_html);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());//, $from_input_id);
    }

    protected function renderSection(F\Section $section, RendererInterface $default_renderer): string
    {
        $inputs_html = $default_renderer->render($section->getInputs());

        $headline_tpl = $this->getTemplate("tpl.headlines.html", true, true);
        $headline_tpl->setVariable("HEADLINE", $section->getLabel());
        $nesting_level = $section->getNestingLevel() + 2;
        if ($nesting_level > 6) {
            $nesting_level = 6;
        };
        $headline_tpl->setVariable("LEVEL", $nesting_level);

        $headline_html = $headline_tpl->get();

        return $this->wrapInFormContext($section, $headline_html, $inputs_html);
    }

    protected function renderUrlField(F\Url $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.url.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderFileField(FI\File $input, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.file.html', true, true);
        foreach ($input->getDynamicInputs() as $metadata_input) {
            $file_info = null;
            if (null !== ($data = $metadata_input->getValue())) {
                $file_id = (!$input->hasMetadataInputs()) ?
                    $data : $data[$input->getUploadHandler()->getFileIdentifierParameterName()] ?? null;

                if (null !== $file_id) {
                    $file_info = $input->getUploadHandler()->getInfoResult($file_id);
                }
            }

            $template = $this->renderFilePreview(
                $input,
                $metadata_input,
                $default_renderer,
                $file_info,
                $template
            );
        }

        $file_preview_template = $this->getTemplate('tpl.file.html', true, true);
        $file_preview_template = $this->renderFilePreview(
            $input,
            $input->getTemplateForDynamicInputs(),
            $default_renderer,
            null,
            $file_preview_template
        );

        $template->setVariable('FILE_PREVIEW_TEMPLATE', $file_preview_template->get('block_file_preview'));

        $this->setHelpBlockForFileField($template, $input);

        $input = $this->initClientsideFileInput($input);

        // display the action button (to choose files).
        $template->setVariable('ACTION_BUTTON', $default_renderer->render(
            $this->getUIFactory()->button()->shy(
                $this->txt('select_files_from_computer'),
                '#'
            )
        ));

        return $this->wrapInFormContext(
            $input,
            $input->getLabel(),
            $template->get(),
        );
    }

    protected function renderHiddenField(F\Hidden $input): string
    {
        $template = $this->getTemplate('tpl.hidden.html', true, true);
        $this->applyName($input, $template);
        $this->applyValue($input, $template);
        if ($input->isDisabled()) {
            $template->setVariable("DISABLED", 'disabled="disabled"');
        }
        $this->bindJSandApplyId($input, $template);
        return $template->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/tagify.min.js');
        $registry->register('assets/css/tagify.css');
        $registry->register('assets/js/tagInput.js');

        $registry->register('assets/js/dropzone.min.js');
        $registry->register('assets/js/dropzone.js');
        $registry->register('assets/js/input.js');
        $registry->register('assets/js/core.js');
        $registry->register('assets/js/file.js');
        $registry->register('assets/js/input.factory.min.js');
    }

    /**
     * @param Input $input
     * @return F\FormInput|JavaScriptBindable
     */
    protected function setSignals(F\FormInput $input)
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
    ): string {
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

    protected function renderFilePreview(
        FI\File $file_input,
        FormInput $metadata_input,
        RendererInterface $default_renderer,
        ?FileInfoResult $file_info,
        Template $template
    ): Template {
        $template->setCurrentBlock('block_file_preview');
        $template->setVariable('REMOVAL_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->close()->withAction("#")
        ));

        if (null !== $file_info) {
            $template->setVariable('FILE_NAME', $file_info->getName());
            $template->setVariable(
                'FILE_SIZE',
                (string) (new DataSize($file_info->getSize(), DataSize::Byte))
            );
        }

        // only render expansion toggles if the input
        // contains actual (unhidden) inputs.
        if ($file_input->hasMetadataInputs()) {
            $template->setVariable('EXPAND_GLYPH', $default_renderer->render(
                $this->getUIFactory()->symbol()->glyph()->expand()->withAction("#")
            ));
            $template->setVariable('COLLAPSE_GLYPH', $default_renderer->render(
                $this->getUIFactory()->symbol()->glyph()->collapse()->withAction("#")
            ));
        }

        $template->setVariable('METADATA_INPUTS', $default_renderer->render($metadata_input));

        $template->parseCurrentBlock();

        return $template;
    }

    protected function initClientsideFileInput(FI\File $input): FI\File
    {
        return $input->withAdditionalOnLoadCode(
            function ($id) use ($input) {
                $current_file_count = count($input->getDynamicInputs());
                $translations = json_encode($input->getTranslations());
                $is_disabled = ($input->isDisabled()) ? 'true' : 'false';
                $php_upload_limit = $this->getUploadLimitResolver()->getPhpUploadLimitInBytes();
                $should_upload_be_chunked = ($input->getMaxFileSize() > $php_upload_limit) ? 'true' : 'false';
                $chunk_size = (int) floor($php_upload_limit * self::FILE_UPLOAD_CHUNK_SIZE_FACTOR);
                return "
                    $(document).ready(function () {
                        il.UI.Input.File.init(
                            '$id',
                            '{$input->getUploadHandler()->getUploadURL()}',
                            '{$input->getUploadHandler()->getFileRemovalURL()}',
                            '{$input->getUploadHandler()->getFileIdentifierParameterName()}',
                            $current_file_count,
                            {$input->getMaxFiles()},
                            {$input->getMaxFileSize()},
                            '{$this->prepareDropzoneJsMimeTypes($input->getAcceptedMimeTypes())}',
                            $is_disabled,
                            $translations,
                            $should_upload_be_chunked,
                            $chunk_size
                        );
                    });
                ";
            }
        );
    }

    /**
     * Appends all given mime-types to a comma-separated string.
     * (that's only necessary due to a dropzone.js bug).
     * @param array<int, string> $mime_types
     */
    protected function prepareDropzoneJsMimeTypes(array $mime_types): string
    {
        $mime_type_string = '';
        foreach ($mime_types as $index => $mime_type) {
            $mime_type_string .= (isset($mime_types[$index + 1])) ? "$mime_type," : $mime_type;
        }

        return $mime_type_string;
    }

    protected function renderColorPickerField(F\ColorPicker $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.colorpicker.html", true, true);
        $this->applyName($component, $tpl);
        $tpl->setVariable('VALUE', $component->getValue());

        $label_id = $this->createId();
        $tpl->setVariable('ID', $label_id);
        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get(), $label_id);
    }

    protected function renderRatingField(F\Rating $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.rating.html", true, true);
        $id = $this->createId();
        $aria_description_id = $id . '_desc';
        $tpl->setVariable('DESCRIPTION_SRC_ID', $aria_description_id);

        $option_count = count(FiveStarRatingScale::cases()) - 1;

        foreach (range($option_count, 1, -1) as $option) {
            $tpl->setCurrentBlock('scaleoption');
            $tpl->setVariable('ARIALABEL', $this->txt($option . 'stars'));
            $tpl->setVariable('OPT_VALUE', (string) $option);
            $tpl->setVariable('OPT_ID', $id . '-' . $option);
            $tpl->setVariable('NAME', $component->getName());
            $tpl->setVariable('DESCRIPTION_ID', $aria_description_id);

            if ($component->getValue() === FiveStarRatingScale::from((int) $option)) {
                $tpl->setVariable("SELECTED", ' checked="checked"');
            }
            if ($component->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }
            $tpl->parseCurrentBlock();
        }

        if (!$component->isRequired()) {
            $tpl->setVariable('NEUTRAL_ID', $id . '-0');
            $tpl->setVariable('NEUTRAL_NAME', $component->getName());
            $tpl->setVariable('NEUTRAL_LABEL', $this->txt('reset_stars'));
            $tpl->setVariable('NEUTRAL_DESCRIPTION_ID', $aria_description_id);

            if ($component->getValue() === FiveStarRatingScale::NONE || is_null($component->getValue())) {
                $tpl->setVariable('NEUTRAL_SELECTED', ' checked="checked"');
            }
        }

        if ($txt = $component->getAdditionalText()) {
            $tpl->setVariable('TEXT', $txt);
        }

        if ($component->isDisabled()) {
            $tpl->touchBlock('disabled');
        }
        if ($average = $component->getCurrentAverage()) {
            $average_title = sprintf($this->txt('rating_average'), $average);
            $tpl->setVariable('AVERAGE_VALUE', $average_title);
            $tpl->setVariable('AVERAGE_VALUE_PERCENT', $average / $option_count * self::CENTUM);
        }

        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());
    }

    private function setHelpBlockForFileField(Template $template, FI\File $input): void
    {
        $template->setCurrentBlock('HELP_BLOCK');

        $template->setCurrentBlock('MAX_FILE_SIZE');
        $template->setVariable('FILE_SIZE_LABEL', $this->txt('file_notice'));
        $template->setVariable('FILE_SIZE_VALUE', new DataSize($input->getMaxFileSize(), DataSize::Byte));
        $template->parseCurrentBlock();

        $template->setCurrentBlock('MAX_FILES');
        $template->setVariable('FILES_LABEL', $this->txt('ui_file_upload_max_nr'));
        $template->setVariable('FILES_VALUE', $input->getMaxFiles());
        $template->parseCurrentBlock();

        $template->parseCurrentBlock();
    }
}
