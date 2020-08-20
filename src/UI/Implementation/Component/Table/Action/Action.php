<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

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
        $valid = [Signal::class, URI::class];
        $this->checkArgListElements("target", $check, $valid, "target class");
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

    public function withRowId(string $value) : I\Action
    {
        $clone = clone $this;

        $target = $clone->getTarget();
        $param = $clone->getParameterName();

        if ($target instanceof Signal) {
            $target->addOption($param, $value);
        }
        if ($target instanceof URI) {
            if ($target->getQuery()) {
                parse_str($target->getQuery(), $params);
            } else {
                $params = [];
            }
            $params[$param] = $value;
            $target = $target->withQuery(http_build_query($params));
        }
        $clone->target = $target;
        return $clone;
    }

    public function getTargetForButton()
    {
        $target = $this->getTarget();
        if ($target instanceof Signal) {
            return $target;
        }
        parse_str($target->getQuery(), $params);
        return $target->getBaseURI() . '?' . http_build_query($params);
    }
}
