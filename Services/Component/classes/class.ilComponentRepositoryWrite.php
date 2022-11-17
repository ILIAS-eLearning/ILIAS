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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\Data\Version;

/**
 * Writeable part of repository interface to ilComponenDataDB.
 */
interface ilComponentRepositoryWrite extends ilComponentRepository
{
    public function setCurrentPluginVersion(string $plugin_id, Version $version, int $db_version): void;

    public function setActivation(string $plugin_id, bool $activated): void;

    public function removeStateInformationOf(string $plugin_id): void;
}
