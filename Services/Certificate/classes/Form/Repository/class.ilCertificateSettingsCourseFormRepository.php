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

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepository implements ilCertificateFormRepository
{
    private readonly ilCertificateSettingsFormRepository $settingsFormFactory;
    private readonly ilTree $tree;

    public function __construct(
        private readonly ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        private readonly ilLanguage $language,
        ilCtrlInterface $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormFactory = null,
        private readonly ilCertificateObjUserTrackingHelper $trackingHelper = new ilCertificateObjUserTrackingHelper(),
        private readonly ilCertificateObjectHelper $objectHelper = new ilCertificateObjectHelper(),
        private readonly ilCertificateObjectLPHelper $lpHelper = new ilCertificateObjectLPHelper(),
        ?ilTree $tree = null,
        private readonly ilSetting $setting = new ilSetting('crs')
    ) {
        global $DIC;

        $this->settingsFormFactory = $settingsFormFactory ?? new ilCertificateSettingsFormRepository(
            $object->getId(),
            $certificatePath,
            $hasAdditionalElements,
            $language,
            $ctrl,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer()
        );
        $this->tree = $tree ?? $DIC->repositoryTree();
    }

    /**
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
            $subitems->setTitleModifier(function ($id) use ($objectHelper, $lpHelper): string {
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

        if ($titlesOfObjectsWithInvalidModes !== []) {
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

    /**
     * @return array{pageformat: string, pagewidth: mixed, pageheight: mixed, margin_body_top: mixed, margin_body_right: mixed, margin_body_bottom: mixed, margin_body_left: mixed, certificate_text: string, subitems: mixed}
     */
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
