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

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepository implements ilCertificateFormRepository
{
    private ilLanguage $language;
    private ilCertificateSettingsFormRepository $settingsFormFactory;
    private ilObjCourse $object;
    private ilObjectLP $learningProgressObject;
    private ilCertificateObjUserTrackingHelper $trackingHelper;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateObjectLPHelper $lpHelper;
    private ilTree $tree;
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
        ?ilObjectLP $learningProgressObject = null,
        ?ilCertificateSettingsFormRepository $settingsFormFactory = null,
        ?ilCertificateObjUserTrackingHelper $trackingHelper = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateObjectLPHelper $lpHelper = null,
        ?ilTree $tree = null,
        ?ilSetting $setting = null
    ) {
        $this->object = $object;

        $this->language = $language;

        if (null === $settingsFormFactory) {
            $settingsFormFactory = new ilCertificateSettingsFormRepository(
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
        $this->settingsFormFactory = $settingsFormFactory;

        if (null === $learningProgressObject) {
            $learningProgressObject = ilObjectLP::getInstance($this->object->getId());
        }
        $this->learningProgressObject = $learningProgressObject;

        if (null === $trackingHelper) {
            $trackingHelper = new ilCertificateObjUserTrackingHelper();
        }
        $this->trackingHelper = $trackingHelper;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $lpHelper) {
            $lpHelper = new ilCertificateObjectLPHelper();
        }
        $this->lpHelper = $lpHelper;

        if (null === $tree) {
            global $DIC;
            $tree = $DIC['tree'];
        }
        $this->tree = $tree;

        if (null === $setting) {
            $setting = new ilSetting('crs');
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

        $objectLearningProgressSettings = new ilLPObjSettings($this->object->getId());

        $mode = $objectLearningProgressSettings->getMode();
        if (!$this->trackingHelper->enabledLearningProgress() || $mode === ilLPObjSettings::LP_MODE_DEACTIVATED) {
            $subitems = new ilRepositorySelector2InputGUI($this->language->txt('objects'), 'subitems', true);

            $formSection = new ilFormSectionHeaderGUI();
            $formSection->setTitle($this->language->txt('cert_form_sec_add_features'));
            $form->addItem($formSection);

            $exp = $subitems->getExplorerGUI();
            $exp->setSkipRootNode(true);
            $exp->setRootId($this->object->getRefId());
            $exp->setTypeWhiteList($this->getLPTypes($this->object->getRefId()));

            $objectHelper = $this->objectHelper;
            $lpHelper = $this->lpHelper;
            $subitems->setTitleModifier(function ($id) use ($objectHelper, $lpHelper) {
                if (null === $id) {
                    return '';
                }
                $obj_id = $objectHelper->lookupObjId((int) $id);
                $olp = $lpHelper->getInstance($obj_id);

                $invalid_modes = $this->getInvalidLPModes();

                $mode = $olp->getModeText($olp->getCurrentMode());

                if (in_array($olp->getCurrentMode(), $invalid_modes, true)) {
                    $mode = '<strong>' . $mode . '</strong>';
                }
                return $objectHelper->lookupTitle($obj_id) . ' (' . $mode . ')';
            });

            $subitems->setRequired(true);
            $form->addItem($subitems);
        }

        return $form;
    }

    /**
     * @param array $formFields
     * @throws ilException
     */
    public function save(array $formFields): void
    {
        $invalidModes = $this->getInvalidLPModes();

        $titlesOfObjectsWithInvalidModes = [];
        $refIds = $formFields['subitems'] ?? [];

        foreach ($refIds as $refId) {
            $objectId = $this->objectHelper->lookupObjId((int) $refId);
            $learningProgressObject = $this->lpHelper->getInstance($objectId);
            $currentMode = $learningProgressObject->getCurrentMode();
            if (in_array($currentMode, $invalidModes, true)) {
                $titlesOfObjectsWithInvalidModes[] = $this->objectHelper->lookupTitle($objectId);
            }
        }

        if (count($titlesOfObjectsWithInvalidModes) > 0) {
            $message = sprintf(
                $this->language->txt('certificate_learning_progress_must_be_active'),
                implode(', ', $titlesOfObjectsWithInvalidModes)
            );
            throw new ilException($message);
        }

        $this->setting->set(
            'cert_subitems_' . $this->object->getId(),
            json_encode($formFields['subitems'] ?? [], JSON_THROW_ON_ERROR)
        );
    }

    public function fetchFormFieldData(string $content): array
    {
        $formFields = $this->settingsFormFactory->fetchFormFieldData($content);

        $formFields['subitems'] = json_decode($this->setting->get(
            'cert_subitems_' . $this->object->getId(),
            json_encode([], JSON_THROW_ON_ERROR)
        ), true, 512, JSON_THROW_ON_ERROR);
        if ($formFields['subitems'] === 'null' || $formFields['subitems'] === null) {
            $formFields['subitems'] = [];
        }
        return $formFields;
    }

    /**
     * @param int $a_parent_ref_id
     * @return string[]
     */
    private function getLPTypes(int $a_parent_ref_id): array
    {
        $result = [];

        $root = $this->tree->getNodeData($a_parent_ref_id);
        $sub_items = $this->tree->getSubTree($root);
        array_shift($sub_items); // remove root

        foreach ($sub_items as $node) {
            if ($this->lpHelper->isSupportedObjectType($node['type'])) {
                $class = $this->lpHelper->getTypeClass($node['type']);
                /** @var ilObjectLP $class */
                $modes = $class::getDefaultModes($this->trackingHelper->enabledLearningProgress());

                if (count($modes) > 1) {
                    $result[] = $node['type'];
                }
            }
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function getInvalidLPModes(): array
    {
        $invalid_modes = [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_UNDEFINED
        ];

        // without active LP the following modes cannot be supported
        if (!$this->trackingHelper->enabledLearningProgress()) {
            // status cannot be set without active LP
            $invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MANUAL;

            // mode cannot be configured without active LP
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MOBS;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_TLT;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_SCORM;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_VISITS; // ?
        }

        return $invalid_modes;
    }
}
