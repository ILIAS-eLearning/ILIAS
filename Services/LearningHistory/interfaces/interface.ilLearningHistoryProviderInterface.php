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

/**
 * Learning history provider interface
 * Providers can add entries to the learning history through this interface
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilLearningHistoryProviderInterface
{
    public function isActive(): bool;

    public function getEntries(int $ts_start, int $ts_end): array;

    // Get name of provider (in user language)
    public function getName(): string;
}
