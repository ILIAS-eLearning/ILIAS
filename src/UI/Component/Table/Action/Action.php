<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Action;

interface Action extends \ILIAS\UI\Component\Component
{
    public function getLabel() : string;

    public function getParameterName() : string;

    /**
     * @return Data\URI | UI\Component\Signal
     */
    public function getTarget();
}
