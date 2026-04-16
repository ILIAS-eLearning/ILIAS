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

use ILIAS\Filesystem\Configuration\FilesystemConfig;

/**
 * Class ilFileServicesSettings
 *
 * @deprecated use \ILIAS\Filesystem\Configuration\FilesystemConfig in bootstrappiung instead
 */
class ilFileServicesSettings implements FilesystemConfig
{
    public function __construct(
        private FilesystemConfig $filesystem_config
    ) {
    }

    public function isByPassAllowedForCurrentUser(): bool
    {
        return $this->filesystem_config->isByPassAllowedForCurrentUser();
    }

    public function isASCIIConvertionEnabled(): bool
    {
        return $this->filesystem_config->isASCIIConvertionEnabled();
    }

    public function getWhiteListedSuffixes(): array
    {
        return $this->filesystem_config->getWhiteListedSuffixes();
    }

    public function getBlackListedSuffixes(): array
    {
        return $this->filesystem_config->getBlackListedSuffixes();
    }

    public function getDefaultWhitelist(): array
    {
        return $this->filesystem_config->getDefaultWhitelist();
    }

    public function getWhiteListNegative(): array
    {
        return $this->filesystem_config->getWhiteListNegative();
    }

    public function getWhiteListPositive(): array
    {
        return $this->filesystem_config->getWhiteListPositive();
    }

    public function getProhibited(): array
    {
        return $this->filesystem_config->getProhibited();
    }
}
