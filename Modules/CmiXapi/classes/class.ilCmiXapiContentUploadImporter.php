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

use ILIAS\FileUpload\DTO\UploadResult as FileUploadResult;
use ILIAS\FileUpload\DTO\ProcessingStatus as FileUploadProcessingStatus;
use ILIAS\FileUpload\Location as FileUploadResultLocation;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Class ilCmiXapiContentUploadImporter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiContentUploadImporter
{
    public const RELATIVE_CONTENT_DIRECTORY_NAMEBASE = 'lm_data/lm_';

    public const RELATIVE_XSD_DIRECTORY = 'Modules/CmiXapi/xml/contentschema';

    public const IMP_FILE_EXTENSION_XML = 'xml';
    public const IMP_FILE_EXTENSION_ZIP = 'zip';

    public const CMI5_XML = 'cmi5.xml';
    public const CMI5_XSD = 'cmi5_v1_CourseStructure.xsd';

    public const TINCAN_XML = 'tincan.xml';
    public const TINCAN_XSD = 'tincan.xsd';

    /**
     * @var string[]
     */
    protected static array $CONTENT_XML_FILENAMES = [
        self::CMI5_XML, self::TINCAN_XML
    ];

    /**
     * @var string[]
     */
    protected static array $CONTENT_XSD_FILENAMES = [
        self::CMI5_XML => self::CMI5_XSD,
        self::TINCAN_XML => self::TINCAN_XSD
    ];

    protected ilObjCmiXapi $object;

    private \ILIAS\DI\Container $dic;

    /**
     * ilCmiXapiContentUploadImporter constructor.
     */
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->object = $object;
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function ensureCreatedObjectDirectory(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$DIC->filesystem()->web()->has($this->getWebDataDirRelativeObjectDirectory())) {
            $DIC->filesystem()->web()->createDir($this->getWebDataDirRelativeObjectDirectory());
        }
    }

    protected function sanitizeObjectDirectory(): void
    {
        ilFileUtils::renameExecutables(
            implode(DIRECTORY_SEPARATOR, [
                ilFileUtils::getWebspaceDir(),
                $this->getWebDataDirRelativeObjectDirectory()
            ])
        );
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    public function importServerFile(string $serverFile): void
    {
        $this->ensureCreatedObjectDirectory();

        $this->handleFile($serverFile);

        $this->sanitizeObjectDirectory();
    }

    /**
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleFile(string $serverFile): void
    {
        $fileInfo = pathinfo($serverFile);

        switch ($fileInfo['extension']) {
            case self::IMP_FILE_EXTENSION_XML:

                $this->handleXmlFile($serverFile);
                break;

            case self::IMP_FILE_EXTENSION_ZIP:

                $this->handleZipContentUpload($serverFile);

                if ($this->hasStoredContentXml()) {
                    $this->handleXmlFile($this->getStoredContentXml());
                }

                break;
        }
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    public function importFormUpload(ilFormPropertyGUI $uploadInput): void
    {
        global $DIC;
        $this->ensureCreatedObjectDirectory();

        $fileData = $DIC->http()->wrapper()->post()->retrieve($uploadInput->getPostVar(), $DIC->refinery()->kindlyTo()->string());

        $uploadResult = $this->getUpload(
            $fileData['tmp_name']
        );

        $this->handleUpload($uploadResult);

        $this->sanitizeObjectDirectory();
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function getUpload(?string $uploadFilePath): FileUploadResult
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($DIC->upload()->hasUploads()) {
            if (!$DIC->upload()->hasBeenProcessed()) {
                $DIC->upload()->process();
            }

            /* @var FileUploadResult $result */

            $results = $DIC->upload()->getResults();

            if (isset($results[$uploadFilePath])) {
                $result = $results[$uploadFilePath];

                if ($result->isOK()) {
                    return $result;
                }

                throw new ilCmiXapiInvalidUploadContentException(
                    'upload processing failed with message ' .
                    '"' . $result->getStatus()->getMessage() . '"'
                );
            }

            throw new ilCmiXapiInvalidUploadContentException('upload lost during processing!');
        }

        throw new ilCmiXapiInvalidUploadContentException('no upload provided!');
    }

    /**
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleUpload(FileUploadResult $uploadResult): void
    {
        switch ($this->fetchFileExtension($uploadResult)) {
            case self::IMP_FILE_EXTENSION_XML:

                $this->handleXmlFileFromUpload($uploadResult->getName(), $uploadResult->getPath());
                break;

            case self::IMP_FILE_EXTENSION_ZIP:

                $this->handleZipContentUpload($uploadResult->getPath());

                if ($this->hasStoredContentXml()) {
                    $this->handleXmlFile($this->getStoredContentXml());
                }

                break;
        }
    }

    /**
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleXmlFile(string $xmlFilePath): void
    {
        $dom = new DOMDocument();
        $dom->load($xmlFilePath);

        switch (basename($xmlFilePath)) {
            case self::CMI5_XML:

                $xsdFilePath = $this->getXsdFilePath(self::CMI5_XSD);
                $this->validateXmlFile($dom, $xsdFilePath);

                $this->initObjectFromCmi5Xml($dom);

                break;

            case self::TINCAN_XML:

                $xsdFilePath = $this->getXsdFilePath(self::TINCAN_XSD);
                $this->validateXmlFile($dom, $xsdFilePath);

                $this->initObjectFromTincanXml($dom);

                break;
        }
    }

    /**
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleXmlFileFromUpload(string $xmlFileName, string $xmlFilePath): void
    {
        $dom = new DOMDocument();
        $dom->load($xmlFilePath);
        switch (basename($xmlFileName)) {
            case self::CMI5_XML:

                $xsdFilePath = $this->getXsdFilePath(self::CMI5_XSD);
                $this->validateXmlFile($dom, $xsdFilePath);

                $this->initObjectFromCmi5Xml($dom);

                break;

            case self::TINCAN_XML:

                $xsdFilePath = $this->getXsdFilePath(self::TINCAN_XSD);
                $this->validateXmlFile($dom, $xsdFilePath);

                $this->initObjectFromTincanXml($dom);

                break;
        }
    }

    /**
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function validateXmlFile(DOMDocument $dom, $xsdFilePath): void
    {
        if (!$dom->schemaValidate($xsdFilePath)) {
            throw new ilCmiXapiInvalidUploadContentException('invalid content xml given!');
        }
    }

    protected function handleZipContentUpload(string $uploadFilePath): void
    {
        $targetPath = $this->getAbsoluteObjectDirectory();
        $zar = new \ZipArchive();
        $zar->open($uploadFilePath);
        $zar->extractTo($targetPath);
        $zar->close();
    }

    protected function getAbsoluteObjectDirectory(): string
    {
        $dirs = [
            ILIAS_ABSOLUTE_PATH,
            ilFileUtils::getWebspaceDir(),
            $this->getWebDataDirRelativeObjectDirectory()
        ];

        return implode(DIRECTORY_SEPARATOR, $dirs);
    }

    public function getWebDataDirRelativeObjectDirectory(): string
    {
        return self::RELATIVE_CONTENT_DIRECTORY_NAMEBASE . $this->object->getId();
    }

    protected function fetchFileExtension(FileUploadResult $uploadResult): string
    {
        return pathinfo($uploadResult->getName(), PATHINFO_EXTENSION);
    }

    protected function hasStoredContentXml(): bool
    {
        return $this->getStoredContentXml() !== '';
    }

    protected function getStoredContentXml(): string
    {
        foreach (self::$CONTENT_XML_FILENAMES as $xmlFileName) {
            $xmlFilePath = $this->getWebDataDirRelativeObjectDirectory() . DIRECTORY_SEPARATOR . $xmlFileName;

            if ($this->dic->filesystem()->web()->has($xmlFilePath)) {
                return $this->getAbsoluteObjectDirectory() . DIRECTORY_SEPARATOR . $xmlFileName;
            }
        }

        return '';
    }

    protected function getXsdFilePath(string $xsdFileName): string
    {
        return ILIAS_ABSOLUTE_PATH . DIRECTORY_SEPARATOR . self::RELATIVE_XSD_DIRECTORY . DIRECTORY_SEPARATOR . $xsdFileName;
    }

    protected function initObjectFromCmi5Xml(DOMDocument $dom): void
    {
        $xPath = new DOMXPath($dom);

        $courseNode = $xPath->query("//*[local-name()='course']")->item(0);
        // TODO: multilanguage support
        $title = $xPath->query("//*[local-name()='title']/*[local-name()='langstring']", $courseNode)->item(0)->nodeValue;
        $this->object->setTitle(trim($title));

        $description = $xPath->query("//*[local-name()='description']/*[local-name()='langstring']", $courseNode)->item(0)->nodeValue;
        $this->object->setDescription(trim($description));

        $publisherId = trim($courseNode->getAttribute('id'));
        $this->object->setPublisherId($publisherId);

        $activityId = $this->generateActivityId($publisherId);
        $this->object->setActivityId($activityId);

        $moveOn = '';

        foreach ($xPath->query("//*[local-name()='au']") as $assignedUnitNode) {
            $relativeLaunchUrl = $xPath->query("//*[local-name()='url']", $assignedUnitNode)->item(0)->nodeValue;
            $launchParameters = $xPath->query("//*[local-name()='launchParameters']", $assignedUnitNode)->item(0)->nodeValue;
            $moveOn = trim($assignedUnitNode->getAttribute('moveOn'));
            $entitlementKey = $xPath->query("//*[local-name()='entitlementKey']", $assignedUnitNode)->item(0)->nodeValue;
            $masteryScore = trim($assignedUnitNode->getAttribute('masteryScore'));

            if (!empty($relativeLaunchUrl)) {
                $this->object->setLaunchUrl(trim($relativeLaunchUrl));
            }
            if (!empty($launchParameters)) {
                $this->object->setLaunchParameters(trim($launchParameters));
            }
            if (!empty($moveOn)) {
                if ($moveOn == ilCmiXapiLP::MOVEON_COMPLETED_AND_PASSED) {
                    $moveOn = ilCmiXapiLP::MOVEON_PASSED;
                }
                $this->object->setMoveOn($moveOn);
            }
            if (!empty($entitlementKey)) {
                $this->object->setEntitlementKey($entitlementKey);
            }
            if (!empty($masteryScore)) {
                $this->object->setMasteryScore((float) $masteryScore);
            } else {
                $this->object->setMasteryScore(ilObjCmiXapi::LMS_MASTERY_SCORE);
            }

            break; // TODO: manage multi au imports
        }
        $xml_str = $dom->saveXML();
        $this->object->setXmlManifest($xml_str);
        $this->object->update();
        $this->object->save();

        $lpSettings = new ilLPObjSettings($this->object->getId());
        $mode = ilLPObjSettings::LP_MODE_DEACTIVATED;

        switch ($moveOn) {
            case ilCmiXapiLP::MOVEON_COMPLETED:
                $mode = ilLPObjSettings::LP_MODE_CMIX_COMPLETED;
                break;
            case ilCmiXapiLP::MOVEON_PASSED:
                $mode = ilLPObjSettings::LP_MODE_CMIX_PASSED;
                break;
            case ilCmiXapiLP::MOVEON_COMPLETED_OR_PASSED:
                $mode = ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED;
                break;
            case ilCmiXapiLP::MOVEON_COMPLETED_AND_PASSED: // ich würde es noch implementieren
                $mode = ilLPObjSettings::LP_MODE_CMIX_PASSED;
                break;
        }
        $lpSettings->setMode($mode);
        $lpSettings->update();
    }

    protected function initObjectFromTincanXml(DOMDocument $dom): void
    {
        $xPath = new DOMXPath($dom);

        foreach ($xPath->query("//*[local-name()='activity']") as $activityNode) {
            $title = $xPath->query("//*[local-name()='name']", $activityNode)->item(0)->nodeValue;
            $this->object->setTitle(trim($title));

            $description = $xPath->query("//*[local-name()='description']", $activityNode)->item(0)->nodeValue;
            $this->object->setDescription(trim($description));

            $activityId = $activityNode->getAttribute('id');
            $this->object->setActivityId(trim($activityId));

            $relativeLaunchUrl = $xPath->query("//*[local-name()='launch']", $activityNode)->item(0)->nodeValue;
            $this->object->setLaunchUrl(trim($relativeLaunchUrl));

            break; // TODO: manage multi activities imports
        }

        $xml_str = $dom->saveXML();
        $this->object->setXmlManifest($xml_str);
        $this->object->update();
        $this->object->save();
    }

    private function generateActivityId(string $publisherId): string
    {
        global $DIC;
        $objId = $this->object->getId();
        return "https://ilias.de/cmi5/activityid/" . (new \Ramsey\Uuid\UuidFactory())->uuid3(ilCmiXapiUser::getIliasUuid(), $objId . '-' . $publisherId);
    }
}
