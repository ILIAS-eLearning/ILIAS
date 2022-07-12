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

use ILIAS\UI\Component\Input\Container\Filter;
use ILIAS\UI\Component\Input\Field\FilterInput;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FilterAdapterGUI
{
    protected bool $expanded;
    protected bool $activated;
    protected string $filter_id;
    protected \ilUIService $ui_service;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $fields = [];
    protected array $field_activations = [];
    /**
     * @var array|string
     */
    protected $class_path;
    protected string $cmd = "";
    protected ?Filter\Standard $filter = null;

    /**
     * @param string|array $class_path
     */
    public function __construct(
        string $filter_id,
        $class_path,
        string $cmd,
        bool $activated = true,
        bool $expanded = true
    ) {
        global $DIC;
        $this->class_path = $class_path;
        $this->cmd = $cmd;
        $this->filter_id = $filter_id;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->ui_service = $DIC->uiService();
        $this->activated = $activated;
        $this->expanded = $expanded;
    }

    public function text(string $key, string $title, bool $activated = true) : self
    {
        $this->addField(
            $key,
            $this->ui->factory()->input()->field()->text($title),
            $activated
        );
        return $this;
    }

    public function select(string $key, string $title, array $options, bool $activated = true) : self
    {
        $this->addField(
            $key,
            $this->ui->factory()->input()->field()->select($title, $options),
            $activated
        );
        return $this;
    }

    protected function addField(string $key, FilterInput $field, bool $activated = true) : void
    {
        $this->fields[$key] = $field;
        $this->field_activations[] = $activated;
        $this->filter = null;
    }

    protected function getFilter() : Filter\Standard
    {
        $ctrl = $this->ctrl;

        if (is_null($this->filter)) {
            $action = $ctrl->getLinkTargetByClass($this->class_path, $this->cmd, "", true);
            $this->filter = $this->ui_service->filter()->standard(
                $this->filter_id,
                $action,
                $this->fields,
                $this->field_activations,
                $this->activated,
                $this->expanded
            );
        }
        return $this->filter;
    }

    public function getData() : ?array
    {
        return $this->ui_service->filter()->getData($this->getFilter());
    }

    public function render() : string
    {
        return $this->ui->renderer()->render($this->getFilter());
    }
}
