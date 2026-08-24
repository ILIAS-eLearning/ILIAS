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

namespace ILIAS\Tracking;

use ILIAS\Tracking\DB\FactoryInterface as DBFactoryInterface;
use ILIAS\Tracking\Export\FactoryInterface as ExportFactoryInterface;
use ILIAS\Tracking\Status\FactoryInterface as StatusFactoryInterface;
use ILIAS\Tracking\View\FactoryInterface as ViewFactoryInterface;

interface FactoryInterface
{
    public function db(): DBFactoryInterface;

    public function export(): ExportFactoryInterface;

    public function view(): ViewFactoryInterface;

    public function status(): StatusFactoryInterface;
}
