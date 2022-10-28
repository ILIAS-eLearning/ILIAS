<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsScormFormRepository implements ilCertificateFormRepository
{
    private ilObject $object;
    private ilLanguage $language;
    private ilCertificateSettingsFormRepository $settingsFormFactory;
    private ilSetting $setting;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrlInterface $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormRepository = null,
        ?ilSetting $setting = null
    ) {
        $this->object = $object;

        $this->language = $language;

        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $object->getId(),
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $ctrl,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }

        $this->settingsFormFactory = $settingsFormRepository;

        if (null === $setting) {
            $setting = new ilSetting('scorm');
        }
        $this->setting = $setting;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @return ilPropertyFormGUI
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI): ilPropertyFormGUI
    {
        $form = $this->settingsFormFactory->createForm($certificateGUI);

        $short_name = new ilTextInputGUI($this->language->txt('certificate_short_name'), 'short_name');
        $short_name->setRequired(true);
        $short_name->setValue(ilStr::subStr($this->object->getTitle(), 0, 30));
        $short_name->setSize(30);

        $short_name_value = $this->setting->get(
            'certificate_short_name_' . $this->object->getId(),
            ''
        );

        $infoText = $this->language->txt('certificate_short_name_description');
        if ($short_name_value !== '') {
            $short_name->setInfo(str_replace(
                '[SHORT_TITLE]',
                $short_name_value,
                $infoText
            ));
        } else {
            $short_name->setInfo($infoText);
        }

        $form->addItem($short_name);

        return $form;
    }

    public function save(array $formFields): void
    {
        $this->setting->set('certificate_' . $this->object->getId(), (string) ($formFields['certificate_enabled_scorm'] ?? '0'));
        $this->setting->set('certificate_short_name_' . $this->object->getId(), (string) ($formFields['short_name'] ?? ''));
    }

    public function fetchFormFieldData(string $content): array
    {
        $formFields = $this->settingsFormFactory->fetchFormFieldData($content);
        $formFields['certificate_enabled_scorm'] = $this->setting->get(
            'certificate_' . $this->object->getId(),
            (string) ($formFields['certificate_enabled_scorm'] ?? '0')
        );
        $formFields['short_name'] = $this->setting->get(
            'certificate_short_name_' . $this->object->getId(),
            (string) ($formFields['short_name'] ?? '')
        );

        return $formFields;
    }
}
