<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class represents an ordering element for assOrderingQuestion
*
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
* @package		Modules/TestQuestionPool
*/
class ilAssOrderingElement
{
    const EXPORT_IDENT_PROPERTY_SEPARATOR = '_';
    
    public static $objectInstanceCounter = 0;
    public $objectInstanceId;
    
    /**
     * this identifier is simply the database row id
     * it should not be used at any place
     *
     * it was never initialised in this object
     * up to now (compare revision)
     *
     * @var integer
     */
    public $id = -1;

    /**
     * this identifier is generated randomly
     * it is recycled for known elements
     *
     * the main purpose is to have a key that does not make the solution
     * derivable and is therefore useable in the examines working form
     *
     * @var integer
     */
    protected $randomIdentifier = null;
    
    /**
     * this identifier is used to identify elements and is stored
     * together with the set position and indentation
     *
     * this happens for the examine's submit data as well, an order index
     * is build and the elements are assigned using this identifier
     *
     * it is an integer sequence starting at 0 that increments
     * with every added element while obsolete numbers are not recycled
     *
     * @var integer
     */
    protected $solutionIdentifier = null;
    
    /**
     * the correct width of indentation for the element
     *
     * @var integer
     */
    protected $indentation = 0;
    
    /**
     * the correct position in the ordering sequence
     *
     * @var integer
     */
    protected $position = null;
    
    /**
     * @var string
     */
    protected $content = null;
    
    /**
     * @var string
     */
    protected $uploadImageName = null;
    
    /**
     * @var string
     */
    protected $uploadImageFile = null;
    
    /**
     * @var bool
     */
    protected $imageRemovalRequest = null;
    
    /**
     * @var string
     */
    protected $imagePathWeb = null;
    
    /**
     * @var string
     */
    protected $imagePathFs = null;
    
    /**
     * @var null
     */
    protected $imageThumbnailPrefix = null;
    
    /**
     * ilAssOrderingElement constructor.
     */
    public function __construct()
    {
        $this->objectInstanceId = ++self::$objectInstanceCounter;
    }
    
    /**
     * Cloning
     */
    public function __clone()
    {
        $this->objectInstanceId = ++self::$objectInstanceCounter;
    }
    
    /**
     * @return ilAssOrderingElement
     */
    public function getClone()
    {
        return clone $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @return integer $randomIdentifier
     */
    public function getRandomIdentifier()
    {
        return $this->randomIdentifier;
    }
    
    /**
     * @param $randomIdentifier
     */
    public function setRandomIdentifier($randomIdentifier)
    {
        $this->randomIdentifier = $randomIdentifier;
    }
    
    /**
     * @return int
     */
    public function getSolutionIdentifier()
    {
        return $this->solutionIdentifier;
    }
    
    /**
     * @param int $solutionIdentifier
     */
    public function setSolutionIdentifier($solutionIdentifier)
    {
        $this->solutionIdentifier = $solutionIdentifier;
    }
    
    /**
     * @param int $indentation
     */
    public function setIndentation($indentation)
    {
        $this->indentation = $indentation;
    }
    
    /**
     * @return int
     */
    public function getIndentation()
    {
        return $this->indentation;
    }
    
    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @return string
     */
    public function getUploadImageFile()
    {
        return $this->uploadImageFile;
    }
    
    /**
     * @param string $uploadImageFile
     */
    public function setUploadImageFile($uploadImageFile)
    {
        $this->uploadImageFile = $uploadImageFile;
    }
    
    /**
     * @return string
     */
    public function getUploadImageName()
    {
        return $this->uploadImageName;
    }
    
    /**
     * @param string $uploadImageName
     */
    public function setUploadImageName($uploadImageName)
    {
        $this->uploadImageName = $uploadImageName;
    }
    
    /**
     * @return bool
     */
    public function isImageUploadAvailable()
    {
        return (bool) strlen($this->getUploadImageFile());
    }
    
    /**
     * @return bool
     */
    public function isImageRemovalRequest()
    {
        return $this->imageRemovalRequest;
    }
    
    /**
     * @param bool $imageRemovalRequest
     */
    public function setImageRemovalRequest($imageRemovalRequest)
    {
        $this->imageRemovalRequest = $imageRemovalRequest;
    }
    
    /**
     * @return string
     */
    public function getImagePathWeb()
    {
        return $this->imagePathWeb;
    }
    
    /**
     * @param string $imagePathWeb
     */
    public function setImagePathWeb($imagePathWeb)
    {
        $this->imagePathWeb = $imagePathWeb;
    }
    
    /**
     * @return string
     */
    public function getImagePathFs()
    {
        return $this->imagePathFs;
    }
    
    /**
     * @param string $imagePathFs
     */
    public function setImagePathFs($imagePathFs)
    {
        $this->imagePathFs = $imagePathFs;
    }
    
    /**
     * @return null
     */
    public function getImageThumbnailPrefix()
    {
        return $this->imageThumbnailPrefix;
    }
    
    /**
     * @param null $imageThumbnailPrefix
     */
    public function setImageThumbnailPrefix($imageThumbnailPrefix)
    {
        $this->imageThumbnailPrefix = $imageThumbnailPrefix;
    }
    
    /**
     * @param ilAssOrderingElement $element
     * @return bool
     */
    public function isSameElement(ilAssOrderingElement $element)
    {
        if ($element->getRandomIdentifier() != $this->getRandomIdentifier()) {
            return false;
        }
        
        if ($element->getSolutionIdentifier() != $this->getSolutionIdentifier()) {
            return false;
        }
        
        if ($element->getPosition() != $this->getPosition()) {
            return false;
        }
        
        if ($element->getIndentation() != $this->getIndentation()) {
            return false;
        }
        
        return true;
    }
    
    public function getStorageValue1($orderingType)
    {
        switch ($orderingType) {
            case OQ_NESTED_TERMS:
            case OQ_NESTED_PICTURES:
                
                return $this->getPosition();
            
            case OQ_TERMS:
            case OQ_PICTURES:
                
                return $this->getSolutionIdentifier();
        }
    }
    
    public function getStorageValue2($orderingType)
    {
        switch ($orderingType) {
            case OQ_NESTED_TERMS:
            case OQ_NESTED_PICTURES:
                
                return $this->getRandomIdentifier() . ':' . $this->getIndentation();
            
            case OQ_TERMS:
            case OQ_PICTURES:
                
                return $this->getPosition() + 1;
        }
    }
    
    public function __toString()
    {
        return $this->getContent();
    }
    
    protected function thumbnailFileExists()
    {
        if (!strlen($this->getContent())) {
            return false;
        }
        
        return file_exists($this->getThumbnailFilePath());
    }
    
    protected function getThumbnailFilePath()
    {
        return $this->getImagePathFs() . $this->getImageThumbnailPrefix() . $this->getContent();
    }
    
    protected function getThumbnailFileUrl()
    {
        return $this->getImagePathWeb() . $this->getImageThumbnailPrefix() . $this->getContent();
    }
    
    protected function imageFileExists()
    {
        if (!strlen($this->getContent())) {
            return false;
        }
        
        return file_exists($this->getImageFilePath());
    }
    
    protected function getImageFilePath()
    {
        return $this->getImagePathFs() . $this->getContent();
    }
    
    protected function getImageFileUrl()
    {
        return $this->getImagePathWeb() . $this->getContent();
    }
    
    public function getPresentationImageUrl()
    {
        if ($this->thumbnailFileExists()) {
            return $this->getThumbnailFileUrl();
        }
        
        if ($this->imageFileExists()) {
            return $this->getImageFileUrl();
        }
        
        return '';
    }

    public function getExportIdent()
    {
        $ident = array(
            $this->getRandomIdentifier(),
            $this->getSolutionIdentifier(),
            $this->getPosition(),
            $this->getIndentation()
        );
        
        return implode(self::EXPORT_IDENT_PROPERTY_SEPARATOR, $ident);
    }
    
    public function isExportIdent($ident)
    {
        if (!strlen($ident)) {
            return false;
        }
        
        $parts = explode(self::EXPORT_IDENT_PROPERTY_SEPARATOR, $ident);
        
        if (count($parts) != 4) {
            return false;
        }
        
        if (!ilAssOrderingElementList::isValidRandomIdentifier($parts[0])) {
            return false;
        }
        
        if (!ilAssOrderingElementList::isValidSolutionIdentifier($parts[1])) {
            return false;
        }
        
        if (!ilAssOrderingElementList::isValidPosition($parts[2])) {
            return false;
        }
        
        if (!ilAssOrderingElementList::isValidIndentation($parts[3])) {
            return false;
        }
        
        return true;
    }
    
    public function setExportIdent($ident)
    {
        if ($this->isExportIdent($ident)) {
            list($randomId, $solutionId, $pos, $indent) = explode(
                self::EXPORT_IDENT_PROPERTY_SEPARATOR,
                $ident
            );
            
            $this->setRandomIdentifier($randomId);
            $this->setSolutionIdentifier($solutionId);
            $this->setPosition($pos);
            $this->setIndentation($indent);
        }
    }
}
