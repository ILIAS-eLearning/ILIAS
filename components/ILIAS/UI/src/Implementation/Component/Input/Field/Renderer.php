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

    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $component = $this->setSignals($component);

        [$context_tpl, $label_id, $error_id, $byline_id] = $this->renderContextPass1($component);
        [$component, $label_html, $input_id, $input_html] = $this->renderInnerPart($component, $default_renderer, $label_id, $error_id, $byline_id);

        return $this->renderContextPass2($component, $context_tpl, $label_id, $label_html, $input_id, $input_html);
    }


    // RENDER CONTEXT

    protected function renderContextPass1(Component\Component $component): array
    {
        $context_tpl = $this->buildContextTemplate();

        // outer div
        if ($component->isDisabled()) {
            $context_tpl->touchBlock("disabled");
        }
        $context_tpl->setVariable("UI_COMPONENT_NAME", $this->getComponentCanonicalNameAttribute($component));
        $context_tpl->setVariable("INPUT_NAME", $component->getName());

        // label
        $label_id = $this->createId();

        $error_id = $this->applyError($component, $context_tpl);
        $byline_id = $this->applyByline($component, $context_tpl);

        return [$context_tpl, $label_id, $error_id ?? null, $byline_id ?? null];
    }

    protected function renderContextPass2(Component\Component $component, Template $context_tpl, string $label_id, ?string $label_html, string $input_id, string $input_html): string
    {
        $context_tpl->setVariable("BINDING_ID", $this->bindJavaScript($component) ?? $this->createId());
        if ($label_html) {
            $context_tpl->setVariable("LABEL_ID", $label_id);
            $context_tpl->setVariable("LABEL", $label_html);
            if ($component->isRequired()) {
                $context_tpl->setVariable("REQUIRED_ARIA", $this->txt('required_field'));
            }
            $context_tpl->setVariable("INPUT_ID", $input_id);
        }
        $context_tpl->setVariable("INPUT", $input_html);
        return $context_tpl->get();
    }

    protected function buildContextTemplate(): Template
    {
        return $this->getTemplate("tpl.context_form.html", true, true);
    }


    // RENDER ACTUAL INPUT

    protected function renderInnerPart(Component\Component $c, RendererInterface $dr, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        return match(get_class($c)) {
            F\Text::class => $this->renderTextField($c, $dr, $label_id, $error_id, $byline_id),
            F\Textarea::class => $this->renderTextareaField($c, $dr, $label_id, $error_id, $byline_id),
            F\Markdown::class => $this->renderMarkdownField($c, $dr, $label_id, $error_id, $byline_id),
            F\Url::class => $this->renderUrlField($c, $dr, $label_id, $error_id, $byline_id),
            F\Numeric::class => $this->renderNumericField($c, $dr, $label_id, $error_id, $byline_id),
            F\Password::class => $this->renderPasswordField($c, $dr, $label_id, $error_id, $byline_id),
            F\DateTime::class => $this->renderDateTimeField($c, $dr, $label_id, $error_id, $byline_id),
            F\ColorPicker::class => $this->renderColorPickerField($c, $dr, $label_id, $error_id, $byline_id),
            F\Checkbox::class => $this->renderCheckboxField($c, $dr, $label_id, $error_id, $byline_id),
            F\Select::class => $this->renderSelectField($c, $dr, $label_id, $error_id, $byline_id),
            F\Hidden::class => $this->renderHiddenField($c, $dr, $label_id, $error_id, $byline_id),
            F\Tag::class => $this->renderTagField($c, $dr, $label_id, $error_id, $byline_id),
            F\Radio::class => $this->renderRadioField($c, $dr, $label_id, $error_id, $byline_id),
            F\Rating::class => $this->renderRatingField($c, $dr, $label_id, $error_id, $byline_id),
            F\MultiSelect::class => $this->renderMultiSelectField($c, $dr, $label_id, $error_id, $byline_id),
            F\File::class => $this->renderFileField($c, $dr, $label_id, $error_id, $byline_id),

            F\Group::class => $this->renderGroup($c, $dr, $label_id, $error_id, $byline_id),
            F\Section::class => $this->renderSection($c, $dr, $label_id, $error_id, $byline_id),
            F\OptionalGroup::class => $this->renderOptionalGroup($c, $dr, $label_id, $error_id, $byline_id),
            F\SwitchableGroup::class => $this->renderSwitchableGroup($c, $dr, $label_id, $error_id, $byline_id),
            F\Link::class => $this->renderLinkField($c, $dr, $label_id, $error_id, $byline_id),
            F\Duration::class => $this->renderDurationField($c, $dr, $label_id, $error_id, $byline_id),


            default => $this->cannotHandleComponent($c)
        };
    }

    protected function renderTextField(F\Text $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.text.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        if ($component->getMaxLength()) {
            $tpl->setVariable("MAX_LENGTH", "maxlength=" . $component->getMaxLength());
        }
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderTextareaField(F\TextArea $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.textarea.html", true, true);
        $this->applyName($component, $tpl);

        $component = $component->withAdditionalOnLoadCode(
            static function ($id): string {
                return "
                    il.UI.Input.textarea.init('$id');
                ";
            }
        );

        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());

        if (0 < $component->getMaxLimit()) {
            $tpl->setVariable('REMAINDER_TEXT', $this->txt('ui_chars_remaining'));
            $tpl->setVariable('REMAINDER', $component->getMaxLimit() - strlen($component->getValue() ?? ''));
            $tpl->setVariable('MAX_LIMIT', $component->getMaxLimit());
        }

        if (null !== $component->getMinLimit()) {
            $tpl->setVariable('MIN_LIMIT', $component->getMinLimit());
        }

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderMarkdownField(F\Markdown $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        [$component, $label_html, $input_id, $input_html] = $this->renderTextareaField($component, $default_renderer, $label_id, $error_id, $byline_id);

        $component = $component->withAdditionalOnLoadCode(
            static function ($id) use ($component, $input_id): string {
                return "
                    il.UI.Input.markdown.init(
                        '$input_id',
                        '{$component->getMarkdownRenderer()->getAsyncUrl()}',
                        '{$component->getMarkdownRenderer()->getParameterName()}'
                    );
                ";
            }
        );

        $markdown_tpl = $this->getTemplate("tpl.markdown.html", true, true);
        $markdown_tpl->setVariable('TEXTAREA', $input_html);

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

        return [$component, $label_html, $input_id, $markdown_tpl->get()];
    }


    protected function renderUrlField(F\Url $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.url.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderNumericField(F\Numeric $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.numeric.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderPasswordField(F\Password $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
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
                        const fieldContainer = document.querySelector('#$id .c-field-password');
                        fieldContainer.classList.add('revealed');
                        fieldContainer.getElementsByTagName('input').item(0).type='text';
                    });" .
                    "$(document).on('$sig_mask', function() {
                        const fieldContainer = document.querySelector('#$id .c-field-password');
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

        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderDateTimeField(F\DateTime $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.datetime.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);

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

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderColorPickerField(F\ColorPicker $component, $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.colorpicker.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderCheckboxField(F\Checkbox $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.checkbox.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    public function renderSelectField(F\Select $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.select.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);

        $value = $component->getValue();
        $value_is_empty = $value === null || $value === '';
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value_is_empty) {
            $tpl->setVariable("SELECTED", 'selected="selected"');
        }
        if ($value_is_empty && $component->isRequired()) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }

        if ($value_is_empty || !$component->isRequired()) {
            $tpl->setVariable("VALUE", null);
            $tpl->setVariable("VALUE_STR", $component->isRequired() ? $this->txt('ui_select_dropdown_label') : '-');
            $tpl->parseCurrentBlock();
        }

        foreach ($component->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if (!$value_is_empty && $value == $option_key) {
                $tpl->setVariable("SELECTED", 'selected="selected"');
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderHiddenField(F\Hidden $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate('tpl.hidden.html', true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $this->applyValue($component, $tpl, $this->escapeSpecialChars());
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderTagField(F\Tag $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.tag_input.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);

        $config = new \StdClass();
        $config->disabled = $component->isDisabled();
        $config->max_tags = $component->getMaxTags();
        $config->tag_max_length = $component->getTagMaxLength();
        $config->user_created_tags_allowed = $component->areUserCreatedTagsAllowed();
        $config->suggestion_starts_after = $component->getSuggestionsStartAfter();
        $config->tags = $component->getTags();
        $config->value = $component->getValue();
        $config = json_encode($config);
        $component = $component->withAdditionalOnLoadCode(
            fn($id) =>
            "il.UI.Input.tagInput.init('{$id}', {$config});"
        );

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderRadioField(F\Radio $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.radio.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $id = $this->applyIDs($tpl, $label_id, $error_id, $byline_id);

        $id_count = 0;
        foreach ($component->getOptions() as $value => $label) {
            $opt_id = $id . '_opt_' . (string) ($id_count++);

            if ($component->getValue() !== null && $component->getValue() == $value) {
                $tpl->touchBlock("option_checked");
            }
            if ($component->isDisabled()) {
                $tpl->touchBlock("option_disabled");
            }

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable("OPTION_NAME", $component->getName());
            $tpl->setVariable("OPTION_ID", $opt_id);
            $tpl->setVariable("OPTION_VALUE", $value);
            $tpl->setVariable("OPTION_LABEL", $label);

            $byline = $component->getBylineFor((string) $value);
            if (!empty($byline)) {
                $tpl->setVariable("OPTION_BYLINE_ID", $byline);
                $tpl->setVariable("OPTION_BYLINE", $byline);
            }

            $tpl->parseCurrentBlock();
        }

        return [$component, $component->getLabel(), $id, $tpl->get()];
    }

    protected function renderRatingField(F\Rating $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.rating.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        $id = $this->applyIDs($tpl, $label_id, $error_id, $byline_id);

        $option_count = count(FiveStarRatingScale::cases()) - 1;

        if ($average = $component->getCurrentAverage()) {
            $average_title = sprintf($this->txt('rating_average'), $average);
            $tpl->setVariable('AVERAGE_VALUE', $average_title);
            $tpl->setVariable('AVERAGE_VALUE_PERCENT', $average / $option_count * self::CENTUM);
        }

        foreach (range($option_count, 1, -1) as $option) {
            if ($component->getValue() === FiveStarRatingScale::from((int) $option)) {
                $tpl->touchBlock("option_checked");
            }
            if ($component->isDisabled()) {
                $tpl->touchBlock("option_disabled");
            }

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable('OPTION_ARIALABEL', $this->txt($option . 'stars'));
            $tpl->setVariable('OPTION_VALUE', (string) $option);
            $tpl->setVariable('OPTION_ID', $id . '-' . $option);
            $tpl->setVariable('OPTION_NAME', $component->getName());
            $tpl->parseCurrentBlock();
        }

        if (!$component->isRequired()) {
            $tpl->setVariable('NEUTRAL_ID', $id . '-0');
            $tpl->setVariable('NEUTRAL_NAME', $component->getName());
            $tpl->setVariable('NEUTRAL_LABEL', $this->txt('reset_stars'));

            if ($component->getValue() === FiveStarRatingScale::NONE || is_null($component->getValue())) {
                $tpl->touchBlock("neutral_checked");
            }
            if ($component->isDisabled()) {
                $tpl->touchBlock("neutral_disabled");
            }
        }

        return [$component, $component->getLabel(), $id, $tpl->get()];
    }

    protected function renderMultiSelectField(F\MultiSelect $component, RendererInterface $_, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        $name = $this->applyName($component, $tpl);
        $id = $this->applyIDs($tpl, $label_id, $error_id, $byline_id);

        $options = $component->getOptions();
        $value = $component->getValue();
        $id_count = 0;
        if (count($options) > 0) {
            foreach ($options as $opt_value => $opt_label) {
                $opt_id = $id . '_opt_' . (string) ($id_count++);

                $tpl->setCurrentBlock('option_block');
                $tpl->setVariable("OPTION_NAME", $name);
                $tpl->setVariable("OPTION_ID", $opt_id);
                $tpl->setVariable("OPTION_VALUE", $opt_value);
                $tpl->setVariable("OPTION_LABEL", $opt_label);

                if ($value && in_array($opt_value, $value)) {
                    $tpl->touchBlock("option_checked");
                }
                $tpl->parseCurrentBlock();
            }
        } else {
            $tpl->touchBlock("no_options");
        }

        return [$component, $component->getLabel(), $id, $tpl->get()];
    }


    protected function renderGroup(F\Group $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.group.html", true, true);
        $this->applyName($component, $tpl);
        $tpl->setVariable("ARIA_LABEL", $component->getLabel());
        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputs()));
        return [$component, null, $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderSection(F\Section $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.section.html", true, true);
        $this->applyName($component, $tpl);
        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputs()));

        $headline_tpl = $this->getTemplate("tpl.headlines.html", true, true);
        $headline_tpl->setVariable("HEADLINE", $component->getLabel());
        $nesting_level = $component->getNestingLevel() + 2;
        if ($nesting_level > 6) {
            $nesting_level = 6;
        };
        $headline_tpl->setVariable("LEVEL", $nesting_level);

        return [$component, $headline_tpl->get(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderOptionalGroup(F\OptionalGroup $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.optionalgroup.html", true, true);
        $this->applyName($component, $tpl);
        $this->applyDisabled($component, $tpl);
        if ($component->getValue()) {
            $tpl->touchBlock("value");
        }

        if ($byline_id) {
            $tpl->setVariable("BYLINE_ID", $byline_id);
        }

        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputs()));

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, null), $tpl->get()];
    }

    protected function renderSwitchableGroup(F\SwitchableGroup $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {

        $tpl = $this->getTemplate("tpl.switchablegroup.html", true, true);
        $this->applyName($component, $tpl);
        $id = $this->applyIDs($tpl, $label_id, $error_id, $byline_id);

        $groupswitch_disabled = $component->getDisabledGroupSwitch();

        $id_count = 0;
        foreach ($component->getInputs() as $value => $group) {
            $opt_id = $id . '_opt_' . (string) ($id_count++);

            if ($component->getValue() !== null && $component->getValue() == $value) {
                $tpl->touchBlock("option_checked");
                if ($groupswitch_disabled) {
                    $group = $group->withDisabled(true);
                }
            }
            if ($component->isDisabled() || $groupswitch_disabled) {
                $tpl->touchBlock("option_disabled");
            }

            $tpl->setCurrentBlock('optionblock');
            $tpl->setVariable("OPTION_NAME", $component->getName());
            $tpl->setVariable("OPTION_ID", $opt_id);
            $tpl->setVariable("OPTION_VALUE", $value);
            $tpl->setVariable("OPTION_LABEL", $group->getLabel());

            if ($groupswitch_disabled && $key == $value) {
                $tpl->setVariable("HIDDEN_NAME", $component->getName());
                $tpl->setVariable("HIDDEN_VAL", (string) $key);
            }

            $tpl->setVariable("INPUTS", $default_renderer->render($group));

            $tpl->parseCurrentBlock();
        }

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderLinkField(F\Link $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.link.html", true, true);
        $this->applyName($component, $tpl);
        $tpl->setVariable("ARIA_LABEL", $component->getLabel());
        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputs()));
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderDurationField(F\Duration $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate("tpl.duration.html", true, true);
        $this->applyName($component, $tpl);
        $tpl->setVariable("ARIA_LABEL", $component->getLabel());
        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputs()));
        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
    }

    protected function renderFileField(F\File $component, RendererInterface $default_renderer, string $label_id, ?string $error_id, ?string $byline_id): array
    {
        $tpl = $this->getTemplate('tpl.file.html', true, true);
        foreach ($component->getDynamicInputs() as $metadata_input) {
            $file_info = null;
            if (null !== ($data = $metadata_input->getValue())) {
                $file_id = (!$component->hasMetadataInputs()) ?
                    $data : $data[$component->getUploadHandler()->getFileIdentifierParameterName()] ?? null;

                if (null !== $file_id) {
                    $file_info = $component->getUploadHandler()->getInfoResult($file_id);
                }
            }

            $tpl = $this->renderFilePreview(
                $component,
                $metadata_input,
                $default_renderer,
                $file_info,
                $tpl
            );
        }

        $file_preview_template = $this->getTemplate('tpl.file.html', true, true);
        $file_preview_template = $this->renderFilePreview(
            $component,
            $component->getTemplateForDynamicInputs(),
            $default_renderer,
            null,
            $file_preview_template
        );

        $tpl->setVariable('FILE_PREVIEW_TEMPLATE', $file_preview_template->get('block_file_preview'));

        $this->setHelpBlockForFileField($tpl, $component);

        $component = $this->initClientsideFileInput($component);

        // display the action button (to choose files).
        $tpl->setVariable('ACTION_BUTTON', $default_renderer->render(
            $this->getUIFactory()->button()->shy(
                $input->getMaxFiles() <= 1
                    ? $this->txt('select_file_from_computer')
                    : $this->txt('select_files_from_computer'),
                '#'
            )
        ));

        return [$component, $component->getLabel(), $this->applyIDs($tpl, $label_id, $error_id, $byline_id), $tpl->get()];
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

    protected function setHelpBlockForFileField(Template $template, FI\File $input): void
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


    // RENDERER HELPERS FOR ACTUAL INPUT

    protected function applyError(FormInput $component, Template $tpl): ?string
    {
        $error = $component->getError();
        if (!$error) {
            return null;
        }

        $error_id = $this->createId();
        $tpl->setVariable("ERROR_ID", $error_id);
        $tpl->setVariable("ERROR_LABEL", $this->txt("ui_error"));
        $tpl->setVariable("ERROR", $error);

        return $error_id;
    }

    protected function applyByline(FormInput $component, Template $tpl): ?string
    {
        $byline = $component->getByline();
        if (!$byline) {
            return null;
        }

        $byline_id = $this->createId();
        $tpl->setVariable("BYLINE_ID", $byline_id);
        $tpl->setVariable("BYLINE", $byline);

        return $byline_id;
    }

    protected function applyName(FormInput $component, Template $tpl): ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function applyDisabled(FormInput $component, Template $tpl): void
    {
        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled");
        }
    }

    protected function applyIds(Template $tpl, string $label_id, ?string $error_id, ?string $byline_id): string
    {
        $id = $this->createId();
        $tpl->setVariable("ID", $id);
        $tpl->setVariable("LABEL_ID", $label_id);
        $describedby = join(" ", array_filter([$error_id, $byline_id]));
        if ($describedby) {
            $tpl->setVariable("DESCRIBED_BY", $describedby);
        }
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

    protected function bindJSandApplyId(Component\JavaScriptBindable $component, Template $tpl): string
    {
        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable("ID", $id);
        return $id;
    }

    protected function escapeSpecialChars(): Closure
    {
        return function ($v) {
            // with declare(strict_types=1) in place,
            // htmlspecialchars will not silently convert to string anymore;
            // therefore, the typecast must be explicit
            return htmlspecialchars((string) $v, ENT_QUOTES, 'utf-8', false);
        };
    }

    protected function htmlEntities(): Closure
    {
        return function ($v) {
            // with declare(strict_types=1) in place,
            // htmlentities will not silently convert to string anymore;
            // therefore, the typecast must be explicit
            return htmlentities((string) $v, ENT_QUOTES, 'utf-8', false);
        };
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
}
