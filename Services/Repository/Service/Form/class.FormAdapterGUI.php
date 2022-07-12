<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Repository\Form;

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\Input\Field\FormInput;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FormAdapterGUI
{
    protected const DEFAULT_SECTION = "@internal_default_section";
    protected string $title = "";
    /**
     * @var mixed|null
     */
    protected $raw_data = null;
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $fields = [];
    protected array $sections = [self::DEFAULT_SECTION => ["title" => "", "description" => ""]];
    protected string $current_section = self::DEFAULT_SECTION;
    protected array $section_of_field = [];
    protected $class_path;
    protected string $cmd = self::DEFAULT_SECTION;
    protected ?Form\Standard $form = null;
    protected array $upload_handler = [];

    /**
     * @param string|array $class_path
     */
    public function __construct(
        $class_path,
        string $cmd
    ) {
        global $DIC;
        $this->class_path = $class_path;
        $this->cmd = $cmd;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function section(
        string $key,
        string $title,
        string $description = ""
    ) : self {
        if ($this->title == "") {
            $this->title = $title;
        }

        $this->sections[$key] = [
            "title" => $title,
            "description" => $description
        ];
        $this->current_section = $key;
        return $this;
    }

    public function text(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->text($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function textarea(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->textarea($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function select(
        string $key,
        string $title,
        array $options,
        string $description = "",
        ?string $value = null
    ) : self {
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

    public function file(
        string $key,
        string $title,
        \Closure $result_handler,
        string $id_parameter,
        int $max_files = 1,
        array $mime_types = []
    ) : self {
        $this->upload_handler[$key] = new \ilRepoStandardUploadHandlerGUI(
            $result_handler,
            $id_parameter
        );

        $field = $this->ui->factory()->input()->field()->file(
            $this->upload_handler[$key],
            $title
        )
            ->withMaxFileSize((int) \ilFileUtils::getUploadSizeLimitBytes())
            ->withMaxFiles($max_files);
        if (count($mime_types) > 0) {
            $field = $field->withAcceptedMimeTypes($mime_types);
        }

        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function getRepoStandardUploadHandlerGUI(string $key) : \ilRepoStandardUploadHandlerGUI
    {
        if (!isset($this->upload_handler[$key])) {
            throw new \ilException("Unknown file upload field: " . $key);
        }
        return $this->upload_handler[$key];
    }


    protected function addField(string $key, FormInput $field) : void
    {
        if (isset($this->section_of_field[$key])) {
            throw new \ilException("Duplicate Input Key: " . $key);
        }
        if ($key === "") {
            throw new \ilException("Missing Input Key: " . $key);
        }
        $this->section_of_field[$key] = $this->current_section;
        $this->fields[$this->current_section][$key] = $field;
        $this->form = null;
    }

    protected function getForm() : Form\Standard
    {
        $ctrl = $this->ctrl;

        if (is_null($this->form)) {
            $action = $ctrl->getLinkTargetByClass($this->class_path, $this->cmd);
            $inputs = [];
            foreach ($this->sections as $sec_key => $section) {
                if ($sec_key === self::DEFAULT_SECTION) {
                    if (isset($this->fields[$sec_key])) {
                        foreach ($this->fields[$sec_key] as $f_key => $field) {
                            $inputs[$f_key] = $field;
                        }
                    }
                } else {
                    if (isset($this->fields[$sec_key]) && count($this->fields[$sec_key]) > 0) {
                        $inputs[$sec_key] = $this->ui->factory()->input()->field()->section(
                            $this->fields[$sec_key],
                            $section["title"],
                            $section["description"]
                        );
                    }
                }
            }
            $this->form = $this->ui->factory()->input()->container()->form()->standard(
                $action,
                $inputs
            );
        }
        return $this->form;
    }

    /**
     * @return mixed
     */
    public function getData(string $key)
    {
        if (is_null($this->raw_data)) {
            $request = $this->http->request();
            $this->raw_data = $this->getForm()->withRequest($request)->getData();
        }

        if (!isset($this->section_of_field[$key])) {
            throw new \ilException("Unknown Key: " . $key);
        }

        $section_data = ($this->section_of_field[$key] === self::DEFAULT_SECTION)
            ? $this->raw_data
            : $this->raw_data[$this->section_of_field[$key]] ?? null;

        if (!isset($section_data[$key])) {
            return null;
        }
        return $section_data[$key];
    }

    public function render() : string
    {
        return $this->ui->renderer()->render($this->getForm());
    }
}
