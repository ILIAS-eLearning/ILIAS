<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumeProviderIcon
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProviderIcon
{
    const MAX_ICON_SIZE = 32;
    
    protected static $RELATIVE_DIRECTORY_PATH = [
        'lti_data', 'provider_icon'
    ];
    
    protected static $SUPPORTED_FILE_EXTENSIONS = [
        'png', 'jpg', 'jpeg'
    ];
    
    /**
     * @var int
     */
    protected $providerId;
    
    /**
     * @var string
     */
    protected $filename;
    
    /**
     * ilLTIConsumeProviderIcon constructor.
     * @param int $providerId
     * @param string $filename
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function __construct(int $providerId, string $filename = '')
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->providerId = $providerId;
        $this->filename = $filename;
        
        $this->ensureExistingRelativeDirectory();
    }
    
    public function buildFilename($fileExtension)
    {
        return "{$this->providerId}.{$fileExtension}";
    }
    
    /**
     * @return string
     */
    public function getFilename() : string
    {
        return $this->filename;
    }
    
    /**
     * @param string $filename
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }
    
    /**
     * @return string
     */
    public function getRelativeDirectory()
    {
        return implode(DIRECTORY_SEPARATOR, self::$RELATIVE_DIRECTORY_PATH);
    }
    
    /**
     * @return string
     */
    public function getRelativeFilePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->getRelativeDirectory(), $this->getFilename()
        ]);
    }
    
    /**
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            \ilUtil::getWebspaceDir(), $this->getRelativeFilePath()
        ]);
    }
    
    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function ensureExistingRelativeDirectory()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (!$DIC->filesystem()->web()->has($this->getRelativeDirectory())) {
            $DIC->filesystem()->web()->createDir($this->getRelativeDirectory());
        }
    }
    
    /**
     * @return bool
     */
    public function exists()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (!strlen($this->getFilename())) {
            return false;
        }
        
        return $DIC->filesystem()->web()->has($this->getRelativeFilePath());
    }
    
    /**
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function delete()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($DIC->filesystem()->web()->has($this->getRelativeFilePath())) {
            $DIC->filesystem()->web()->delete($this->getRelativeFilePath());
        }
        
        $this->setFilename('');
    }
    
    protected function convert()
    {
        // convert to square with same side length (optimal for tile view)
        
        list($width, $height, $type, $attr) = getimagesize($this->getAbsoluteFilePath());
        $minSize = min($width, $height);
        
        if (self::MAX_ICON_SIZE) {
            $minSize = min($minSize, self::MAX_ICON_SIZE);
        }
        
        $convertCmd = "{$this->getAbsoluteFilePath()}[0]";
        $convertCmd .= " -geometry {$minSize}x{$minSize}^ -gravity center";
        $convertCmd .= " -extent {$minSize}x{$minSize}";
        $convertCmd .= " {$this->getAbsoluteFilePath()}";
        
        ilUtil::execConvert($convertCmd);
    }
    
    /**
     * @param string $uploadFile
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function save(string $uploadFile)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($DIC->upload()->hasUploads()) {
            if (!$DIC->upload()->hasBeenProcessed()) {
                $DIC->upload()->process();
            }
            
            /* @var \ILIAS\FileUpload\DTO\UploadResult $result */
            
            $results = $DIC->upload()->getResults();
            
            if (isset($results[$uploadFile])) {
                $result = $results[$uploadFile];
                
                if ($result->isOK()) {
                    $fileExtentsion = pathinfo($result->getName(), PATHINFO_EXTENSION);
                    $this->setFilename($this->buildFilename($fileExtentsion));
                    
                    $DIC->upload()->moveOneFileTo(
                        $result,
                        $this->getRelativeDirectory(),
                        \ILIAS\FileUpload\Location::WEB,
                        $this->getFileName(),
                        true
                    );
                    
                    $this->convert();
                }
            }
        }
    }
    
    /**
     * @param ilImageFileInputGUI $fileInput
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function handleUploadInputSubission(ilImageFileInputGUI $fileInput)
    {
        if ($fileInput->getDeletionFlag()) {
            $this->delete();
        }
        
        // ilImageFileInputGUI does NOT come with a set value that could be fetched with
        // $fileInput->getValue(). Instead ilImageFileInputGUI provides upload info in $_POST.
        $fileData = $_POST[$fileInput->getPostVar()];
        
        if ($fileData['tmp_name']) {
            $this->save($fileData['tmp_name']);
        }
    }
    
    /**
     * @return array
     */
    public static function getSupportedFileExtensions()
    {
        return self::$SUPPORTED_FILE_EXTENSIONS;
    }
}
