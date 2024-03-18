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

namespace ILIAS\components\ResourceStorage\Container\Wrapper;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
final class Dir extends Item
{
    public function __construct(
        string $path_inside_zip,
        string $title,
        private \DateTimeImmutable $modification_date,
    ) {
        parent::__construct($path_inside_zip, $title);
    }

    public function getModificationDate(): \DateTimeImmutable
    {
        return $this->modification_date;
    }

}
