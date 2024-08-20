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
    public const DYNAMIC_INPUT_ID_PLACEHOLDER = 'DYNAMIC_INPUT_ID';

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

            case ($component instanceof F\Group):
            case ($component instanceof F\Link):
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
        string $input_html,
        RendererInterface $default_renderer,
        string $id_pointing_to_input = '',
        string $dependant_group_html = '',
        bool $bind_label_with_for = true
    ): string {
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);

        $tpl->setVariable("INPUT", $input_html);

        if ($id_pointing_to_input && $bind_label_with_for) {
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
            $tpl->setVariable("ERROR_FOR_ID", $id_pointing_to_input);
        }

        $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        return $tpl->get();
    }

    protected function maybeDisable(FormInput $component, Template $tpl): void
    {
        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", 'disabled="disabled"');
        }
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

    protected function renderTextField(F\Text $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.text.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getMaxLength()) {
            $tpl->setVariable("MAX_LENGTH", $component->getMaxLength());
        }

        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderNumericField(F\Numeric $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.numeric.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderCheckboxField(F\Checkbox $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        $this->applyName($component, $tpl);

        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }

        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderOptionalGroup(F\OptionalGroup $component, RendererInterface $default_renderer): string
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
            return "il.UI.Input.groups.optional.init('$id')";
        });
        $this->bindJSandApplyId($component, $tpl);

        $dependant_group_html = $default_renderer->render($component->getInputs());

        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id, $dependant_group_html);
    }

    protected function renderSwitchableGroup(F\SwitchableGroup $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.radio.html", true, true);

        /**
         * @var $component F\SwitchableGroup
         */
        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return "il.UI.Input.groups.switchable.init('$id')";
        });
        $id = $this->bindJSandApplyId($component, $tpl);

        foreach ($component->getInputs() as $key => $group) {
            $opt_id = $id . '_' . $key . '_opt';

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable("NAME", $component->getName());
            $tpl->setVariable("OPTIONID", $opt_id);
            $tpl->setVariable("VALUE", $key);
            $tpl->setVariable("LABEL", $group->getLabel());
            $tpl->setVariable("BYLINE", $group->getByline());

            if ($component->getValue() !== null) {
                list($index, ) = $component->getValue();
                if ($index == $key) {
                    $tpl->setVariable("CHECKED", 'checked="checked"');
                }
            }

            $dependant_group_html = $default_renderer->render($group);
            $tpl->setVariable("DEPENDANT_FIELDS", $dependant_group_html);

            if ($component->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }
            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer);
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
        $id = $this->bindJSandApplyId($component, $tpl);

        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", "disabled");
            $tpl->setVariable("READONLY", "readonly");
        }

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
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
                $container_id = $id . "_container";
                return
                    "$(document).on('$sig_reveal', function() {
                        $('#$container_id').addClass('revealed');
                        $('#$container_id')[0].getElementsByTagName('input')[0].type='text';
                    });" .
                    "$(document).on('$sig_mask', function() {
                        $('#$container_id').removeClass('revealed');
                        $('#$container_id')[0].getElementsByTagName('input')[0].type='password';
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
        $tpl->setVariable('ID_CONTAINER', $id . "_container");
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
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

        if(!($value && $component->isRequired())) {
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

        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderMarkdownField(F\Markdown $component, RendererInterface $default_renderer): string
    {
        /** @var $component F\Markdown */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id) use ($component): string {
                return "
                    il.UI.Input.markdown.init(
                        '$id',
                        '{$component->getMarkdownRenderer()->getAsyncUrl()}',
                        '{$component->getMarkdownRenderer()->getParameterName()}'
                    );
                ";
            }
        );

        $textarea_tpl = $this->getPreparedTextareaTemplate($component);
        $textarea_id = $this->bindJSandApplyId($component, $textarea_tpl);

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

            $action = $this->getUIFactory()->button()->standard($default_renderer->render($glyph), '#');

            if ($component->isDisabled()) {
                $action = $action->withUnavailableAction();
            }

            $markdown_tpl->setVariable($tpl_variable, $default_renderer->render($action));
        }

        // label must point to the wrapped textarea input, not the markdown input.
        return $this->wrapInFormContext($component, $markdown_tpl->get(), $default_renderer, $textarea_id);
    }

    protected function renderTextareaField(F\Textarea $component, RendererInterface $default_renderer): string
    {
        /** @var $component F\Textarea */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id): string {
                return "
                    il.UI.Input.textarea.init('$id');
                ";
            }
        );

        $tpl = $this->getPreparedTextareaTemplate($component);
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
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
        $this->maybeDisable($component, $tpl);

        return $tpl;
    }

    protected function renderRadioField(F\Radio $component, RendererInterface $default_renderer): string
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

            $byline = $component->getBylineFor((string) $value);
            if (!empty($byline)) {
                $tpl->setVariable("BYLINE", $byline);
            }

            $tpl->parseCurrentBlock();
        }

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer);
    }

    protected function renderMultiSelectField(F\MultiSelect $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        $id = $this->bindJSandApplyId($component, $tpl);
        $tpl->setVariable("ID", $id);

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

                if ($component->isDisabled()) {
                    $tpl->setVariable("DISABLED", 'disabled="disabled"');
                }
                $tpl->parseCurrentBlock();
            }
        } else {
            $tpl->touchBlock("no_options");
        }

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer);
    }

    protected function renderDateTimeField(F\DateTime $component, RendererInterface $default_renderer): string
    {
        list($component, $tpl) = $this->internalRenderDateTimeField($component, $default_renderer);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
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
        $this->maybeDisable($component, $tpl);

        return [$component, $tpl];
    }

    protected function renderDurationField(F\Duration $component, RendererInterface $default_renderer): string
    {
        $inputs = $component->getInputs();

        $input = array_shift($inputs); //from
        list($input, $tpl) = $this->internalRenderDateTimeField($input, $default_renderer);
        $first_input_id = $this->bindJSandApplyId($input, $tpl);
        $input_html = $this->wrapInFormContext($input, $tpl->get(), $default_renderer, $first_input_id);

        $input = array_shift($inputs) //until
            ->withAdditionalPickerconfig(['useCurrent' => false]);
        $input_html .= $default_renderer->render($input);

        $tpl = $this->getTemplate("tpl.duration.html", true, true);
        $id = $this->bindJSandApplyId($component, $tpl);
        $tpl->setVariable('DURATION', $input_html);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer);
    }

    protected function renderSection(F\Section $section, RendererInterface $default_renderer): string
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

    protected function renderUrlField(F\Url $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.url.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        $this->maybeDisable($component, $tpl);
        $id = $this->bindJSandApplyId($component, $tpl);
        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
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

        $this->setHelpBlockForFileField($template, $input);

        $input = $this->initClientsideFileInput($input);
        $input = $this->initClientsideRenderer($input, $file_preview_template->get('block_file_preview'));

        // display the action button (to choose files).
        $template->setVariable('ACTION_BUTTON', $default_renderer->render(
            $this->getUIFactory()->button()->shy(
                $this->txt('select_files_from_computer'),
                '#'
            )
        ));

        $js_id = $this->bindJSandApplyId($input, $template);
        return $this->wrapInFormContext(
            $input,
            $template->get(),
            $default_renderer,
            $js_id,
            "",
            false
        );
    }

    protected function renderHiddenField(F\Hidden $input): string
    {
        $template = $this->getTemplate('tpl.hidden.html', true, true);
        $this->applyName($input, $template);
        $this->applyValue($input, $template);
        $this->maybeDisable($input, $template);
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
        $registry->register('assets/js/file.js');
        $registry->register('assets/js/groups.js');
        $registry->register('assets/js/dynamic_inputs_renderer.js');
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

    protected function initClientsideRenderer(
        FI\HasDynamicInputs $input,
        string $template_html
    ): FI\HasDynamicInputs {
        $dynamic_inputs_template_html = $this->replaceTemplateIds($template_html);
        $dynamic_input_count = count($input->getDynamicInputs());

        // note that $dynamic_inputs_template_html is in tilted single quotes (`),
        // because otherwise the html syntax might collide with normal ones.
        return $input->withAdditionalOnLoadCode(function ($id) use (
            $dynamic_inputs_template_html,
            $dynamic_input_count
        ) {
            return "
                $(document).ready(function () {
                    il.UI.Input.DynamicInputsRenderer.init(
                        '$id',
                        `$dynamic_inputs_template_html`,
                        $dynamic_input_count
                    );
                });
            ";
        });
    }

    protected function replaceTemplateIds(string $template_html): string
    {
        // regex matches anything between 'id="' and '"', hence the js_id.
        preg_match_all('/(?<=id=")(.*?)(?=\s*")/', $template_html, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $index => $js_id) {
                $template_html = str_replace(
                    $js_id,
                    self::DYNAMIC_INPUT_ID_PLACEHOLDER . "_$index",
                    $template_html
                );
            }
        }

        return $template_html;
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
        $id = $this->bindJSandApplyId($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer, $id);
    }

    protected function renderRatingField(F\Rating $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.rating.html", true, true);
        $id = $this->bindJSandApplyId($component, $tpl);
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

        if(!$component->isRequired()) {
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


        return $this->wrapInFormContext($component, $tpl->get(), $default_renderer);
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
