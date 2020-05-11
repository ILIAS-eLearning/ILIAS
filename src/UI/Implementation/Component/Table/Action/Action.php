<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Action as I;

abstract class Action implements I\Action
{
    use ComponentHelper;

    protected $label;
    protected $target;
    protected $parameter_name;

    public function __construct(
        string $label,
        string $parameter_name,
        $target
    ) {
        $this->label = $label;
        $this->parameter_name = $parameter_name;

        $check = [$target];
        $this->checkArgListElements("target", $check, self::VALID_TARGET_CLASSES, "target class");
        $this->target = $target;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getParameterName() : string
    {
        return $this->parameter_name;
    }

    /*
     * @inheritdoc
     */
    public function getTarget()
    {
        return $this->target;
    }
}
