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

namespace ILIAS\Repository\Form;

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FormAdapterGUI
{
    use StdObjProperties;

    protected const DEFAULT_SECTION = "@internal_default_section";
    protected bool $in_modal = false;
    protected string $submit_caption = "";
    protected \ilLanguage $lng;
    protected const ASYNC_NONE = 0;
    protected const ASYNC_MODAL = 1;
    protected const ASYNC_ON = 2;
    protected \ILIAS\Data\Factory $data;
    protected \ilObjUser $user;
    protected string $last_key = "";
    protected \ILIAS\Refinery\Factory $refinery;

    protected string $title = "";


    /**
     * @var mixed|null
     */
    protected $raw_data = null;
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $fields = [];
    protected array $field_path = [];
    protected array $sections = [self::DEFAULT_SECTION => ["title" => "", "description" => "", "fields" => []]];
    protected string $current_section = self::DEFAULT_SECTION;
    protected array $section_of_field = [];
    protected $class_path;
    protected string $cmd = self::DEFAULT_SECTION;
    protected ?Form\Standard $form = null;
    protected array $upload_handler = [];
    protected int $async_mode = self::ASYNC_NONE;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected ?array $current_switch = null;
    protected ?array $current_optional = null;
    protected ?array $current_group = null;
    protected static bool $initialised = false;

    /**
     * @param string|array $class_path
     */
    public function __construct(
        $class_path,
        string $cmd,
        string $submit_caption = ""
    ) {
        global $DIC;
        $this->class_path = $class_path;
        $this->cmd = $cmd;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->data = new \ILIAS\Data\Factory();
        $this->submit_caption = $submit_caption;
        self::initJavascript();
        $this->initStdObjProperties($DIC);
    }

    public static function getOnLoadCode(): string
    {
        return "il.repository.ui.init();\n" .
            "il.repository.core.init('" . ILIAS_HTTP_PATH . "')";
    }

    public static function initJavascript(): void
    {
        global $DIC;

        if (!isset($DIC["ui.factory"])) {
            return;
        }

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        if (!self::$initialised) {
            $main_tpl = $DIC->ui()->mainTemplate();
            $main_tpl->addJavaScript("assets/js/repository.js");
            $main_tpl->addOnLoadCode(self::getOnLoadCode());

            // render dummy components to load the necessary .js needed for async processing
            $d = [];
            $d[] = $f->input()->field()->text("");
            $r->render($d);
            self::$initialised = true;
        }
    }

    public function asyncModal(): self
    {
        $this->async_mode = self::ASYNC_MODAL;
        $this->in_modal = true;
        return $this;
    }

    public function async(): self
    {
        $this->async_mode = self::ASYNC_ON;
        return $this;
    }

    public function syncModal(): self
    {
        $this->in_modal = true;
        return $this;
    }

    public function isSentAsync(): bool
    {
        return ($this->async_mode !== self::ASYNC_NONE);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function section(
        string $key,
        string $title,
        string $description = ""
    ): self {
        if ($this->title == "") {
            $this->title = $title;
        }

        $this->sections[$key] = [
            "title" => $title,
            "description" => $description,
            "fields" => []
        ];
        $this->current_section = $key;
        return $this;
    }

    public function text(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->text($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function checkbox(
        string $key,
        string $title,
        string $description = "",
        ?bool $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->checkbox($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function hidden(
        string $key,
        string $value
    ): self {
        $field = $this->ui->factory()->input()->field()->hidden();
        $field = $field->withValue($value);
        $this->addField($key, $field);
        return $this;
    }

    public function required($required = true): self
    {
        if ($required && ($field = $this->getLastField())) {
            $field = $field->withRequired(true);
            $this->replaceLastField($field);
        }
        return $this;
    }

    public function disabled($disabled = true): self
    {
        if ($disabled && ($field = $this->getLastField())) {
            $field = $field->withDisabled(true);
            $this->replaceLastField($field);
        }
        return $this;
    }

    public function textarea(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->textarea($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function number(
        string $key,
        string $title,
        string $description = "",
        ?int $value = null,
        ?int $min_value = null,
        ?int $max_value = null
    ): self {
        $trans = [];
        if (!is_null($min_value)) {
            $trans[] = $this->refinery->int()->isGreaterThanOrEqual($min_value);
        }
        if (!is_null($max_value)) {
            $trans[] = $this->refinery->int()->isLessThanOrEqual($max_value);
        }
        $field = $this->ui->factory()->input()->field()->numeric($title, $description);
        if (count($trans) > 0) {
            $field = $field->withAdditionalTransformation($this->refinery->logical()->parallel($trans));
        }
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function date(
        string $key,
        string $title,
        string $description = "",
        ?\ilDate $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->dateTime($title, $description);

        $format = $this->user->getDateFormat();
        $dt_format = (string) $format;

        $field = $field->withFormat($format);
        if (!is_null($value)) {
            $field = $field->withValue(
                (new \DateTime($value->get(IL_CAL_DATE)))->format($dt_format)
            );
        }
        $this->addField($key, $field);
        return $this;
    }

    public function dateTime(
        string $key,
        string $title,
        string $description = "",
        ?\ilDateTime $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->dateTime($title, $description)->withUseTime(true);

        if ((int) $this->user->getTimeFormat() === \ilCalendarSettings::TIME_FORMAT_12) {
            $dt_format = $this->data->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $dt_format = $this->data->dateFormat()->withTime24($this->user->getDateFormat());
        }
        $field = $field->withFormat($dt_format);
        if (!is_null($value) && !is_null($value->get(IL_CAL_DATETIME))) {
            $field = $field->withValue(
                (new \DateTime($value->get(IL_CAL_DATETIME)))->format(
                    ((string) $dt_format)
                )
            );
        }
        $this->addField($key, $field);
        return $this;
    }

    public function dateTimeDuration(
        string $key,
        string $title,
        string $description = "",
        ?\ilDateTime $from = null,
        ?\ilDateTime $to = null,
        string $label_from = "",
        string $label_to = ""
    ): self {
        if ($label_from === "") {
            $label_from = $this->lng->txt("rep_activation_limited_start");
        }
        if ($label_to === "") {
            $label_to = $this->lng->txt("rep_activation_limited_end");
        }
        $field = $this->ui->factory()->input()->field()->duration($title, $description)->withUseTime(true)->withLabels($label_from, $label_to);

        if ((int) $this->user->getTimeFormat() === \ilCalendarSettings::TIME_FORMAT_12) {
            $dt_format = $this->data->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $dt_format = $this->data->dateFormat()->withTime24($this->user->getDateFormat());
        }
        $field = $field->withFormat($dt_format);
        $val_from = $val_to = null;
        if (!is_null($from) && !is_null($from->get(IL_CAL_DATETIME))) {
            $val_from = (new \DateTime(
                $from->get(IL_CAL_DATETIME)
            ))->format((string) $dt_format);
        }
        if (!is_null($to) && !is_null($to->get(IL_CAL_DATETIME))) {
            $val_to = (new \DateTime(
                $to->get(IL_CAL_DATETIME)
            ))->format((string) $dt_format);
        }
        $field = $field->withValue([$val_from, $val_to]);
        $this->addField($key, $field);
        return $this;
    }

    /**
     * @return null|\ilDate|\ilDateTime
     */
    protected function getDateTimeData(?\DateTimeImmutable $value, $use_time = false)
    {
        if (is_null($value)) {
            return null;
        }
        if ($use_time) {
            return new \ilDateTime($value->format("Y-m-d H:i:s"), IL_CAL_DATETIME);
        }
        return new \ilDate($value->format("Y-m-d"), IL_CAL_DATE);
    }

    public function select(
        string $key,
        string $title,
        array $options,
        string $description = "",
        ?string $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->select($title, $options, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function radio(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ): self {
        $field = $this->ui->factory()->input()->field()->radio($title, $description);
        if (!is_null($value)) {
            $field = $field->withOption($value, "");    // dummy to prevent exception, will be overwritten by radioOption
            $field = $field->withValue($value);
        }
        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function radioOption(string $value, string $title, string $description = ""): self
    {
        if ($field = $this->getLastField()) {
            $field = $field->withOption($value, $title, $description);
            $this->replaceLastField($field);
        }
        return $this;
    }

    public function switch(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ): self {
        $this->current_switch = [
            "key" => $key,
            "title" => $title,
            "description" => $description,
            "value" => $value,
            "groups" => []
        ];
        return $this;
    }

    public function optional(
        string $key,
        string $title,
        string $description = "",
        ?bool $value = null
    ): self {
        $this->current_optional = [
            "key" => $key,
            "title" => $title,
            "description" => $description,
            "value" => $value,
            "group" => []
        ];
        return $this;
    }

    public function group(string $key, string $title, string $description = "", $disabled = false): self
    {
        $this->endCurrentGroup();
        $this->current_group = [
            "key" => $key,
            "title" => $title,
            "description" => $description,
            "disabled" => $disabled,
            "fields" => []
        ];
        return $this;
    }

    protected function endCurrentGroup(): void
    {
        if (!is_null($this->current_group)) {
            if (!is_null($this->current_switch)) {
                $this->current_switch["groups"][$this->current_group["key"]] =
                    $this->ui->factory()->input()->field()->group(
                        $this->current_group["fields"],
                        $this->current_group["title"]
                    )->withByline($this->current_group["description"])
                    ->withDisabled($this->current_group["disabled"]);
            }
        }
        $this->current_group = null;
    }

    public function end(): self
    {
        $this->endCurrentGroup();
        if (!is_null($this->current_switch)) {
            $field = $this->ui->factory()->input()->field()->switchableGroup(
                $this->current_switch["groups"],
                $this->current_switch["title"],
                $this->current_switch["description"]
            );
            if (!is_null($this->current_switch["value"])) {
                $field = $field->withValue($this->current_switch["value"]);
            }
            $key = $this->current_switch["key"];
            $this->current_switch = null;
            $this->addField($key, $field);
        }
        if (!is_null($this->current_optional)) {
            $field = $this->ui->factory()->input()->field()->optionalGroup(
                $this->current_optional["fields"],
                $this->current_optional["title"],
                $this->current_optional["description"]
            );
            if ($this->current_optional["value"]) {
                $value = [];
                foreach ($this->current_optional["fields"] as $key => $input) {
                    $value[$key] = $input->getValue();
                }
                $field = $field->withValue($value);
            } else {
                $field = $field->withValue(null);
            }
            $key = $this->current_optional["key"];
            $this->current_optional = null;
            $this->addField($key, $field);
        }
        return $this;
    }

    public function file(
        string $key,
        string $title,
        \Closure $result_handler,
        string $id_parameter,
        string $description = "",
        ?int $max_files = null,
        array $mime_types = [],
        array $ctrl_path = [],
        string $logger_id = ""
    ): self {
        $this->upload_handler[$key] = new \ilRepoStandardUploadHandlerGUI(
            $result_handler,
            $id_parameter,
            $logger_id,
            $ctrl_path
        );

        if (count($mime_types) > 0) {
            $description .= $this->lng->txt("rep_allowed_types") . ": " .
                implode(", ", $mime_types);
        }

        $field = $this->ui->factory()->input()->field()->file(
            $this->upload_handler[$key],
            $title,
            $description
        )
            ->withMaxFileSize((int) \ilFileUtils::getPhpUploadSizeLimitInBytes());
        if (!is_null($max_files)) {
            $field = $field->withMaxFiles($max_files);
        }
        if (count($mime_types) > 0) {
            $field = $field->withAcceptedMimeTypes($mime_types);
        }

        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function getRepoStandardUploadHandlerGUI(string $key): \ilRepoStandardUploadHandlerGUI
    {
        if (!isset($this->upload_handler[$key])) {
            throw new \ilException("Unknown file upload field: " . $key);
        }
        return $this->upload_handler[$key];
    }


    protected function addField(string $key, FormInput $field, $supress_0_key = false): void
    {
        if ($key === "") {
            throw new \ilException("Missing Input Key: " . $key);
        }
        if (isset($this->field[$key])) {
            throw new \ilException("Duplicate Input Key: " . $key);
        }
        $field_path = [];
        if ($this->current_section !== self::DEFAULT_SECTION) {
            $field_path[] = $this->current_section;
        }
        if (!is_null($this->current_group)) {
            $this->current_group["fields"][$key] = $field;
            if (!is_null($this->current_switch)) {
                $field_path[] = $this->current_switch["key"];
                $field_path[] = 1;  // the value of subitems in SwitchableGroup are in the 1 key of the raw data
                $field_path[] = $key;
            }
        } elseif (!is_null($this->current_optional)) {
            $field_path[] = $this->current_optional["key"];
            $this->current_optional["fields"][$key] = $field;
            $field_path[] = $key;
        } else {
            $this->sections[$this->current_section]["fields"][] = $key;
            $field_path[] = $key;
            if ($field instanceof \ILIAS\UI\Component\Input\Field\SwitchableGroup) {
                $field_path[] = 0;      // the value of the SwitchableGroup is in the 0 key of the raw data
            }
            if ($field instanceof \ILIAS\UI\Component\Input\Field\OptionalGroup) {
                //$field_path[] = 0;      // the value of the SwitchableGroup is in the 0 key of the raw data
            }
            if ($field instanceof \ILIAS\UI\Component\Input\Field\File) {
                if (!$supress_0_key) {      // needed for tiles, that come with a custom transformation omitting the 0
                    $field_path[] = 0;      // the value of File Inputs is in the 0 key of the raw data
                }
            }
        }
        $this->fields[$key] = $field;
        $this->field_path[$key] = $field_path;
        $this->last_key = $key;
        $this->form = null;
    }

    protected function getFieldForKey(string $key): FormInput
    {
        if (!isset($this->fields[$key])) {
            throw new \ilException("Unknown Key: " . $key);
        }
        return $this->fields[$key];
    }

    protected function getLastField(): ?FormInput
    {
        return $this->fields[$this->last_key] ?? null;
    }

    protected function replaceLastField(FormInput $field): void
    {
        if ($this->last_key !== "") {
            $this->fields[$this->last_key] = $field;
        }
    }

    protected function getForm(): Form\Standard
    {
        $ctrl = $this->ctrl;

        if (is_null($this->form)) {
            $async = ($this->async_mode !== self::ASYNC_NONE);
            $action = "";
            if (!is_null($this->class_path)) {
                $action = $ctrl->getLinkTargetByClass($this->class_path, $this->cmd, "", $async);
            }
            $inputs = [];
            foreach ($this->sections as $sec_key => $section) {
                if ($sec_key === self::DEFAULT_SECTION) {
                    foreach ($this->sections[$sec_key]["fields"] as $f_key) {
                        $inputs[$f_key] = $this->getFieldForKey($f_key);
                    }
                } elseif (count($this->sections[$sec_key]["fields"]) > 0) {
                    $sec_inputs = [];
                    foreach ($this->sections[$sec_key]["fields"] as $f_key) {
                        $sec_inputs[$f_key] = $this->getFieldForKey($f_key);
                    }
                    $inputs[$sec_key] = $this->ui->factory()->input()->field()->section(
                        $sec_inputs,
                        $section["title"],
                        $section["description"]
                    );
                }
            }
            $this->form = $this->ui->factory()->input()->container()->form()->standard(
                $action,
                $inputs
            );
            if ($this->submit_caption !== "") {
                $this->form = $this->form->withSubmitLabel($this->submit_caption);
            }
        }
        return $this->form;
    }

    public function getSubmitLabel(): string
    {
        return $this->getForm()->getSubmitLabel() ?? $this->lng->txt("save");
    }

    protected function _getData(): void
    {
        if (is_null($this->raw_data)) {
            $request = $this->http->request();
            $this->form = $this->getForm()->withRequest($request);
            $this->raw_data = $this->form->getData();
        }
    }

    public function isValid(): bool
    {
        $this->_getData();
        return !(is_null($this->raw_data));
    }

    /**
     * @return mixed
     */
    public function getData(string $key)
    {
        $this->_getData();

        if (!isset($this->fields[$key])) {
            return null;
        }

        $value = $this->raw_data;
        foreach ($this->field_path[$key] as $path_key) {
            if (!isset($value[$path_key])) {
                return null;
            }
            $value = $value[$path_key];
        }

        $field = $this->getFieldForKey($key);

        if ($field instanceof \ILIAS\UI\Component\Input\Field\DateTime) {
            /** @var \ILIAS\UI\Component\Input\Field\DateTime $field */
            $value = $this->getDateTimeData($value, $field->getUseTime());
        }

        if ($field instanceof \ILIAS\UI\Component\Input\Field\Duration) {
            /** @var \ILIAS\UI\Component\Input\Field\DateTime $field */
            $value = [
                $this->getDateTimeData($value["start"], $field->getUseTime()),
                $this->getDateTimeData($value["end"], $field->getUseTime()),
            ];
        }

        if ($field instanceof \ILIAS\UI\Component\Input\Field\OptionalGroup) {
            $value = is_array($value);
        }

        return $value;
    }

    public function render(): string
    {
        if ($this->async_mode === self::ASYNC_NONE && !$this->ctrl->isAsynch()) {
            $html = $this->ui->renderer()->render($this->getForm());
        } else {
            $html = $this->ui->renderer()->renderAsync($this->getForm()) . "<script>" . $this->getOnLoadCode() . "</script>";
        }
        if ($this->in_modal) {
            if ($this->async_mode === self::ASYNC_MODAL) {
                $html = str_replace("<form ", "<form data-rep-modal-form='async' ", $html);
            } else {
                $html = str_replace("<form ", "<form data-rep-modal-form='sync' ", $html);
            }
        }
        return $html;
    }
}
