<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Component\Table\Column;

/**
 * A Column describes the form of presentation for a certain aspect of data,
 * i.e. a field of a record within a table.
 */
interface Column
{
    public function getTitle() : string;

    public function getType() : string;

    public function withIsSortable(bool $flag) : Column;

    public function isSortable() : bool;

    public function withIsOptional(bool $flag) : Column;

    public function isOptional() : bool;

    public function withIsInitiallyVisible(bool $flag) : Column;

    public function isInitiallyVisible() : bool;
}
