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

namespace ILIAS\Notes;

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\Input\Field\FormInput;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FormAdapterGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $fields = [];
    protected $class_path;
    protected string $cmd = "";
    protected ?Form\Standard $form = null;

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

    protected function addField(string $key, FormInput $field) : void
    {
        $this->fields[$key] = $field;
        $this->form = null;
    }

    protected function getForm() : Form\Standard
    {
        $ctrl = $this->ctrl;

        if (is_null($this->form)) {
            $action = $ctrl->getLinkTargetByClass($this->class_path, $this->cmd);
            $this->form = $this->ui->factory()->input()->container()->form()->standard(
                $action,
                $this->fields
            );
        }
        return $this->form;
    }

    public function getData() : ?array
    {
        $request = $this->http->request();
        return $this->getForm()->withRequest($request)->getData();
    }

    public function render() : string
    {
        return $this->ui->renderer()->render($this->getForm());
    }
}
