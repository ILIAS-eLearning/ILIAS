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

namespace ILIAS\UI\Component\Table\Action;

use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

interface Action extends \ILIAS\UI\Component\Component
{
    public function getLabel(): string;
    /**
     * Each Row (see RowBuilder) has an id; when triggering an action,
     * those ids will be relayed by this parameter.
     */
    public function getParameterName(): string;
    public function getTarget(): Signal|URI;
}
