<?php declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCloneAction
{
    private ilLogger $logger;
    private ilCertificatePathFactory $pathFactory;
    private ilCertificateTemplateRepository $templateRepository;
    private ilDBInterface $database;
    private Filesystem $fileSystem;
    private ilCertificateObjectHelper $objectHelper;
    private string $webDirectory;
    private string $global_certificate_path;

    public function __construct(
        ilDBInterface $database,
        ilCertificatePathFactory $pathFactory,
        ilCertificateTemplateRepository $templateRepository,
        ?Filesystem $fileSystem = null,
        ?ilLogger $logger = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        string $webDirectory = CLIENT_WEB_DIR,
        string $global_certificate_path = null
    ) {
        $this->database = $database;
        $this->pathFactory = $pathFactory;
        $this->templateRepository = $templateRepository;

        if (null === $logger) {
            global $DIC;
            $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        if (null === $fileSystem) {
            global $DIC;
            $fileSystem = $DIC->filesystem()->web();
        }
        $this->fileSystem = $fileSystem;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $global_certificate_path) {
            $global_certificate_path = str_replace(
                '[CLIENT_WEB_DIR]',
                '',
                ilObjCertificateSettingsAccess::getBackgroundImagePath(true)
            );
        }
        $this->global_certificate_path = $global_certificate_path;


        $this->webDirectory = $webDirectory;
    }

    /**
     * @param ilObject $oldObject
     * @param ilObject $newObject
     * @param string   $iliasVersion
     * @param string   $webDir
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
    ) : void {
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
            $backgroundImagePath = $template->getBackgroundImagePath();
            $backgroundImageFile = basename($backgroundImagePath);
            $backgroundImageThumbnail = dirname($backgroundImagePath) . '/background.jpg.thumb.jpg';

            $newBackgroundImage = '';
            $newBackgroundImageThumbnail = '';
            if ($this->global_certificate_path !== $backgroundImagePath) {
                if ($this->fileSystem->has($backgroundImagePath) &&
                    !$this->fileSystem->hasDir($backgroundImagePath)
                ) {
                    $newBackgroundImage = $certificatePath . $backgroundImageFile;
                    $newBackgroundImageThumbnail = str_replace(
                        $webDir,
                        '',
                        $this->getBackgroundImageThumbPath($certificatePath)
                    );
                    if ($this->fileSystem->has($newBackgroundImage) &&
                        !$this->fileSystem->hasDir($newBackgroundImage)
                    ) {
                        $this->fileSystem->delete($newBackgroundImage);
                    }

                    $this->fileSystem->copy(
                        $backgroundImagePath,
                        $newBackgroundImage
                    );
                }

                if (
                    $newBackgroundImageThumbnail !== '' &&
                    $this->fileSystem->has($backgroundImageThumbnail) &&
                    !$this->fileSystem->hasDir($backgroundImageThumbnail)
                ) {
                    if ($this->fileSystem->has($newBackgroundImageThumbnail) &&
                        !$this->fileSystem->hasDir($newBackgroundImageThumbnail)
                    ) {
                        $this->fileSystem->delete($newBackgroundImageThumbnail);
                    }

                    $this->fileSystem->copy(
                        $backgroundImageThumbnail,
                        $newBackgroundImageThumbnail
                    );
                }
            } else {
                $newBackgroundImage = $this->global_certificate_path;
            }

            $newCardThumbImage = '';
            $cardThumbImagePath = $template->getThumbnailImagePath();

            if ($this->fileSystem->has($cardThumbImagePath) && !$this->fileSystem->hasDir($cardThumbImagePath)) {
                $newCardThumbImage = $certificatePath . basename($cardThumbImagePath);
                if ($this->fileSystem->has($newCardThumbImage) && !$this->fileSystem->hasDir($newCardThumbImage)) {
                    $this->fileSystem->delete($newCardThumbImage);
                }
                $this->fileSystem->copy(
                    $cardThumbImagePath,
                    $newCardThumbImage
                );
            }

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
                $newBackgroundImage,
                $newCardThumbImage
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

    private function isActive(int $objectId) : bool
    {
        $sql = 'SELECT 1 FROM il_certificate WHERE obj_id = ' . $this->database->quote($objectId, 'integer');

        if ($row = $this->database->fetchAssoc($this->database->query($sql))) {
            return true;
        }

        return false;
    }

    private function getBackgroundImageName() : string
    {
        return "background.jpg";
    }

    private function getBackgroundImageThumbPath(string $certificatePath) : string
    {
        return $this->webDirectory . $certificatePath . $this->getBackgroundImageName() . ".thumb.jpg";
    }
}
