<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\FileUpload\DTO\UploadResult as FileUploadResult;
use ILIAS\FileUpload\DTO\ProcessingStatus as FileUploadProcessingStatus;
use ILIAS\FileUpload\Location as FileUploadResultLocation;

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
    const RELATIVE_CONTENT_DIRECTORY_NAMEBASE = 'lm_data/lm_';
    
    const RELATIVE_XSD_DIRECTORY = 'Modules/CmiXapi/xml/contentschema';
    
    const IMP_FILE_EXTENSION_XML = 'xml';
    const IMP_FILE_EXTENSION_ZIP = 'zip';
    
    const CMI5_XML = 'cmi5.xml';
    const CMI5_XSD = 'cmi5_v1_CourseStructure.xsd';
    
    const TINCAN_XML = 'tincan.xml';
    const TINCAN_XSD = 'tincan.xsd';
    
    /**
     * @var string[]
     */
    protected static $CONTENT_XML_FILENAMES = [
        self::CMI5_XML, self::TINCAN_XML
    ];
    
    /**
     * @var string[]
     */
    protected static $CONTENT_XSD_FILENAMES = [
        self::CMI5_XML => self::CMI5_XSD,
        self::TINCAN_XML => self::TINCAN_XSD
    ];
    
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    
    /**
     * ilCmiXapiContentUploadImporter constructor.
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;
    }
    
    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function ensureCreatedObjectDirectory()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (!$DIC->filesystem()->web()->has($this->getWebDataDirRelativeObjectDirectory())) {
            $DIC->filesystem()->web()->createDir($this->getWebDataDirRelativeObjectDirectory());
        }
    }
    
    protected function sanitizeObjectDirectory()
    {
        ilUtil::renameExecutables(implode(DIRECTORY_SEPARATOR, [
            \ilUtil::getWebspaceDir(), $this->getWebDataDirRelativeObjectDirectory()
        ]));
    }
    
    /**
     * @param $serverFile
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    public function importServerFile($serverFile)
    {
        $this->ensureCreatedObjectDirectory();
        
        $this->handleFile($serverFile);
        
        $this->sanitizeObjectDirectory();
    }
    
    /**
     * @param string $serverFile
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleFile(string $serverFile)
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
     * @param ilFileInputGUI $uploadInput
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    public function importFormUpload(ilFileInputGUI $uploadInput)
    {
        $this->ensureCreatedObjectDirectory();
        
        $fileData = $_POST[$uploadInput->getPostVar()];
        
        $uploadResult = $this->getUpload(
            $fileData['tmp_name']
        );
        
        $this->handleUpload($uploadResult);
        
        $this->sanitizeObjectDirectory();
    }
    
    /**
     * @param $uploadFilePath
     * @return FileUploadResult
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function getUpload($uploadFilePath)
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
                
                if ($result->getStatus() == FileUploadProcessingStatus::OK) {
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
     * @param FileUploadResult $uploadResult
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleUpload(FileUploadResult $uploadResult)
    {
        switch ($this->fetchFileExtension($uploadResult)) {
            case self::IMP_FILE_EXTENSION_XML:
                
                $this->handleXmlFile($uploadResult->getName());
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
     * @param string $xmlFilePath
     * @throws ilCmiXapiInvalidUploadContentException
     */
    protected function handleXmlFile($xmlFilePath)
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
    
    protected function validateXmlFile(DOMDocument $dom, $xsdFilePath)
    {
        if (!$dom->schemaValidate($xsdFilePath)) {
            throw new ilCmiXapiInvalidUploadContentException('invalid content xml given!');
        }
    }
    
    protected function handleZipContentUpload($uploadFilePath)
    {
        $targetPath = $this->getAbsoluteObjectDirectory();
        $zar = new ZipArchive();
        $zar->open($uploadFilePath);
        $zar->extractTo($targetPath);
        $zar->close();
    }
    
    /**
     * @return string
     */
    protected function getAbsoluteObjectDirectory()
    {
        $dirs = [
            ILIAS_ABSOLUTE_PATH,
            ilUtil::getWebspaceDir(),
            $this->getWebDataDirRelativeObjectDirectory()
        ];
        
        return implode(DIRECTORY_SEPARATOR, $dirs);
    }
    
    /**
     * @return string
     */
    public function getWebDataDirRelativeObjectDirectory()
    {
        return self::RELATIVE_CONTENT_DIRECTORY_NAMEBASE . $this->object->getId();
    }
    
    /**
     * @param FileUploadResult $uploadResult
     * @return mixed
     */
    protected function fetchFileExtension(FileUploadResult $uploadResult)
    {
        return pathinfo($uploadResult->getName(), PATHINFO_EXTENSION);
    }
    
    /**
     * @return bool
     */
    protected function hasStoredContentXml()
    {
        return $this->getStoredContentXml() !== '';
    }
    
    /**
     * @return string
     */
    protected function getStoredContentXml()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        foreach (self::$CONTENT_XML_FILENAMES as $xmlFileName) {
            $xmlFilePath = $this->getWebDataDirRelativeObjectDirectory() . DIRECTORY_SEPARATOR . $xmlFileName;
            
            if ($DIC->filesystem()->web()->has($xmlFilePath)) {
                return $this->getAbsoluteObjectDirectory() . DIRECTORY_SEPARATOR . $xmlFileName;
            }
        }
        
        return '';
    }
    
    /**
     * @param string $xsdFileName
     * @return string
     */
    protected function getXsdFilePath($xsdFileName)
    {
        return ILIAS_ABSOLUTE_PATH . DIRECTORY_SEPARATOR . self::RELATIVE_XSD_DIRECTORY . DIRECTORY_SEPARATOR . $xsdFileName;
    }
    
    protected function initObjectFromCmi5Xml($dom)
    {
        $xPath = new DOMXPath($dom);
        
        $courseNode = $xPath->query("//*[local-name()='course']")->item(0);
        
        $title = $xPath->query("//*[local-name()='title']", $courseNode)->item(0)->nodeValue;
        $this->object->setTitle(trim($title));
        
        $description = $xPath->query("//*[local-name()='description']", $courseNode)->item(0)->nodeValue;
        $this->object->setDescription(trim($description));
        
        $activityId = $courseNode->getAttribute('id');
        $this->object->setActivityId(trim($activityId));
        
        foreach ($xPath->query("//*[local-name()='au']") as $assignedUnitNode) {
            $relativeLaunchUrl = $xPath->query("//*[local-name()='url']", $assignedUnitNode)->item(0)->nodeValue;
            $this->object->setLaunchUrl(trim($relativeLaunchUrl));
            
            break; // TODO: manage multi au imports
        }
        
        $this->object->update();
        $this->object->save();
    }
    
    protected function initObjectFromTincanXml($dom)
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
        
        
        $this->object->update();
        $this->object->save();
    }
}
