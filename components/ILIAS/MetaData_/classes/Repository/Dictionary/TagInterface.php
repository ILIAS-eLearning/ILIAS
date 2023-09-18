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

namespace ILIAS\MetaData\Repository\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\Tags\TagInterface as BaseTagInterface;

interface TagInterface extends BaseTagInterface
{
    public function create(): string;

    public function read(): string;

    public function update(): string;

    public function delete(): string;

    public function table(): string;

    /**
     * The id of 'parent' elements is needed to access their sub-elements
     * in the database (until the next parent in the hierarchy comes up).
     */
    public function isParent(): bool;

    /**
     * @return string[]
     */
    public function expectedParameters(): \Generator;
}
