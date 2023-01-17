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

namespace ILIAS\Services\ResourceStorage\Resources\Listing;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * Move to ENUM as soon as possible
 */
class SortDirection
{
    public const BY_TITLE_ASC = 1;
    public const BY_TITLE_DESC = 2;
    public const BY_SIZE_ASC = 3;
    public const BY_SIZE_DESC = 4;
    public const BY_CREATION_DATE_ASC = 5;
    public const BY_CREATION_DATE_DESC = 6;
}
