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

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;

class ilObjectService
{
    public function __construct(
        private ilDBInterface $database,
        private ilLanguage $language,
        private Filesystem $filesystem,
        private FileUpload $upload,
        private ilObjectCustomIconFactory $custom_icon_factory
    ) {
    }

    /**
     *
     * @deprecated 11: This Settings Instance will be removed with ILIAS 11.
     * Please use ObjectProperties in ilObject.
     */
    public function commonSettings(): ilObjectCommonSettings
    {
        return new ilObjectCommonSettings(
            $this->language,
            $this->upload,
            new ilObjectAdditionalPropertiesLegacyRepository(
                $this->custom_icon_factory,
                $this->filesystem,
                $this->upload
            )
        );
    }
}
