<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Action;

use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

interface Action extends \ILIAS\UI\Component\Component
{

    const VALID_TARGET_CLASSES = [Signal::class, URI::class];

    public function getLabel() : string;
    public function getParameterName() : string;
    /**
     * @return Data\URI | UI\Component\Signal
     */
    public function getTarget();
}
