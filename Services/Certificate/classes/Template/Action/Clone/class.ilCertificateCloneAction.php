<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCloneAction
{
    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @var ilCertificatePathFactory
     */
    private $pathFactory;

    /**
     * @var ilCertificateTemplateRepository
     */
    private $templateRepository;

    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var \ILIAS\Filesystem\Filesystem|null
     */
    private $fileSystem;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var string
     */
    private $webDirectory;
    private $global_certificate_path;

    /**
     * @var ilObjCertificateSettings
     */
    private $global_certificate_settings;

    /**
     * @param ilDBInterface $database
     * @param ilCertificateFactory $certificateFactory
     * @param ilCertificateTemplateRepository $templateRepository
     * @param \ILIAS\Filesystem\Filesystem|null $fileSystem
     * @param illLogger $logger
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param string $rootDirectory
     */
    public function __construct(
        ilDBInterface $database,
        ilCertificatePathFactory $pathFactory,
        ilCertificateTemplateRepository $templateRepository,
        \ILIAS\Filesystem\Filesystem $fileSystem = null,
        ilLogger $logger = null,
        ilCertificateObjectHelper $objectHelper = null,
        ?ilObjCertificateSettings $global_certificate_settings = null,
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

        if (!$global_certificate_settings) {
            $global_certificate_settings = new ilObjCertificateSettings();
        }
        $this->global_certificate_settings = $global_certificate_settings;


        if (null === $global_certificate_path) {
            $global_certificate_path = $this->global_certificate_settings->getDefaultBackgroundImagePath(true);
        }
        $this->global_certificate_path = $global_certificate_path;

        $this->webDirectory = $webDirectory;
    }

    /**
     * @param ilObject $oldObject
     * @param ilObject $newObject
     * @param string $iliasVersion
     * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilDatabaseException
     * @throws ilException
     */
    public function cloneCertificate(
        ilObject $oldObject,
        ilObject $newObject,
        string $iliasVersion = ILIAS_VERSION_NUMERIC,
        string $webDir = CLIENT_WEB_DIR
    ) {
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
                $this->objectHelper->lookupType((int) $newObject->getId()),
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
        if ($this->readActive($oldObject->getId())) {
            $this->database->replace(
                'il_certificate',
                array('obj_id' => array('integer', $newObject->getId())),
                array()
            );
        }
    }

    /**
     * @param integer $objectId
     * @return int
     */
    private function readActive(int $objectId) : int
    {
        $sql = 'SELECT obj_id FROM il_certificate WHERE obj_id = ' . $this->database->quote($objectId, 'integer');

        $query = $this->database->query($sql);

        return $this->database->numRows($query);
    }

    /**
     * Returns the filename of the background image
     *
     * @return string The filename of the background image
     */
    private function getBackgroundImageName()
    {
        return "background.jpg";
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     * @param $certificatePath
     * @return string The filesystem path of the background image thumbnail
     */
    private function getBackgroundImageThumbPath(string $certificatePath) : string
    {
        return $this->webDirectory . $certificatePath . $this->getBackgroundImageName() . ".thumb.jpg";
    }
}
