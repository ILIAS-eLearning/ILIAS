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
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCloneAction
{
    private readonly Filesystem $fileSystem;
    private readonly ilCertificateObjectHelper $objectHelper;

    public function __construct(
        private readonly ilDBInterface $database,
        private readonly ilCertificatePathFactory $pathFactory,
        private readonly ilCertificateTemplateRepository $templateRepository,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilObjCertificateSettings $global_certificate_settings = null,
    ) {
        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     */
    public function cloneCertificate(
        ilObject $oldObject,
        ilObject $newObject,
        string $iliasVersion = ILIAS_VERSION_NUMERIC,
        string $webDir = CLIENT_WEB_DIR
    ): void {
        $oldType = $oldObject->getType();
        $newType = $newObject->getType();

        if ($oldType !== $newType) {
            throw new ilException(sprintf(
                'The types "%s" and "%s" for cloning  does not match',
                $oldType,
                $newType
            ));
        }

        $certificatePath = $this->pathFactory->create($newObject);

        $templates = $this->templateRepository->fetchCertificateTemplatesByObjId($oldObject->getId());

        /** @var ilCertificateTemplate $template */
        foreach ($templates as $template) {
            $newTemplate = new ilCertificateTemplate(
                $newObject->getId(),
                $this->objectHelper->lookupType($newObject->getId()),
                $template->getCertificateContent(),
                $template->getCertificateHash(),
                $template->getTemplateValues(),
                $template->getVersion(),
                $iliasVersion,
                time(),
                $template->isCurrentlyActive(),
                $template->getBackgroundImageIdentification(),
                $template->getThumbnailImageIdentification()
            );

            $this->templateRepository->save($newTemplate);
        }

        // #10271
        if ($this->isActive($oldObject->getId())) {
            $this->database->replace(
                'il_certificate',
                ['obj_id' => ['integer', $newObject->getId()]],
                []
            );
        }
    }

    private function isActive(int $objectId): bool
    {
        $sql = 'SELECT 1 FROM il_certificate WHERE obj_id = ' . $this->database->quote($objectId, 'integer');

        return (bool) $this->database->fetchAssoc($this->database->query($sql));
    }
}
