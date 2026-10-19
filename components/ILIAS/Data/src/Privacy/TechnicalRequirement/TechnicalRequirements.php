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

namespace ILIAS\Data\Privacy\TechnicalRequirement;

use ILIAS\Data\Privacy\Source\DbTarget;

/**
 * Factory for the available requirements. A stateless factory: create it
 * with `new` or inject it. All phrases are required; they are rendered in
 * the generated privacy documentation, so they must be literal strings or
 * constants.
 */
class TechnicalRequirements
{
    public function storeInTable(DbTarget $target, string $in_order_to, string $condition = ''): StoreInTable
    {
        return new StoreInTable($target, $in_order_to, $condition);
    }

    public function displayToUser(string $where, string $to, string $in_order_to, string $condition = ''): DisplayToUser
    {
        return new DisplayToUser($where, $to, $in_order_to, $condition);
    }

    public function export(string $where, string $by, string $in_order_to, string $condition = ''): Export
    {
        return new Export($where, $by, $in_order_to, $condition);
    }

    public function passToComponent(string $component, string $in_order_to): PassToComponent
    {
        return new PassToComponent($component, $in_order_to);
    }

    public function technicalProcessing(string $in_order_to): TechnicalProcessing
    {
        return new TechnicalProcessing($in_order_to);
    }
}
