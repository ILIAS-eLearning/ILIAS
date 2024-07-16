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

namespace ILIAS\Test\Certificate;

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class CertificateSettingsTestFormRepository implements \ilCertificateFormRepository
{
    private readonly \ilCertificateSettingsFormRepository $settings_form_factory;

    public function __construct(
        int $object_id,
        string $certificate_path,
        bool $has_additional_elements,
        \ilLanguage $language,
        \ilCtrlInterface $ctrl,
        \ilAccess $access,
        \ilToolbarGUI $toolbar,
        \ilCertificatePlaceholderDescription $placeholder_description_object,
        ?\ilCertificateSettingsFormRepository $settings_form_repository = null
    ) {
        global $DIC;

        $this->settings_form_factory = $settings_form_repository ?? new \ilCertificateSettingsFormRepository(
            $object_id,
            $certificate_path,
            $has_additional_elements,
            $language,
            $ctrl,
            $access,
            $toolbar,
            $placeholder_description_object,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer()
        );
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws \ilDatabaseException
     * @throws \ilException
     * @throws \ilWACException
     */
    public function createForm(\ilCertificateGUI $certificateGUI): \ilPropertyFormGUI
    {
        return $this->settings_form_factory->createForm($certificateGUI);
    }

    public function save(array $formFields): void
    {
    }

    /**
     * @return array{pageformat: string, pagewidth: mixed, pageheight: mixed, margin_body_top: mixed, margin_body_right: mixed, margin_body_bottom: mixed, margin_body_left: mixed, certificate_text: string}
     */
    public function fetchFormFieldData(string $content): array
    {
        return $this->settings_form_factory->fetchFormFieldData($content);
    }
}
