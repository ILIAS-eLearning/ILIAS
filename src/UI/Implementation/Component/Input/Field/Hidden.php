<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Refinery\Factory;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class Hidden extends Input implements \ILIAS\UI\Component\Input\Field\Hidden
{
    public function __construct(DataFactory $data_factory, Factory $refinery)
    {
        parent::__construct($data_factory, $refinery, '', null);
    }

    public function getUpdateOnLoadCode() : Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement() : ?Constraint
    {
        return null;
    }

    protected function isClientSideValueOk($value) : bool
    {
        return true;
    }
}