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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Button\Month;
use ILIAS\UI\Component\Dropdown\Standard as StandardDropdown;
use ILIAS\UI\Component\Input\ViewControl as ViewControlInterface;

class Section extends ViewControlInput implements ViewControlInterface\Section
{
    protected Button $previous_action;
    protected Button|Month|StandardDropdown $button;
    protected Button $next_action;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        Button $previous_action,
        Button|Month|StandardDropdown $button,
        Button $next_action
    ) {
        parent::__construct($data_factory, $refinery);
        $this->previous_action = $previous_action;
        $this->button = $button;
        $this->next_action = $next_action;
    }

    protected function isClientSideValueOk($value): bool
    {
        return $value === null;
    }

    public function getPreviousActions(): Button
    {
        return $this->previous_action;
    }

    public function getNextActions(): Button
    {
        return $this->next_action;
    }

    public function getSelectorButton(): Button|Month|StandardDropdown
    {
        return $this->button;
    }
}
