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

namespace ILIAS\KioskMode;

use ILIAS\Data;

/**
 * The URLBuilder allows views to get links that are used somewhere inline in
 * the content.
 */
interface URLBuilder
{
    /**
     * Get an URL for the provided command and params.
     */
    public function getURL(string $command, ?int $param = null): Data\URI;
}
