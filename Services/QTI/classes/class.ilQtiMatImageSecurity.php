<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once 'Services/Utilities/classes/class.ilFileUtils.php';
require_once 'Services/QTI/exceptions/class.ilQtiException.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilQtiMatImageSecurity
{
    /**
     * @var ilQTIMatimage
     */
    protected $imageMaterial;
    
    /**
     * @var string
     */
    protected $detectedMimeType;
    
    public function __construct(ilQTIMatimage $imageMaterial)
    {
        $this->setImageMaterial($imageMaterial);
        
        if (!strlen($this->getImageMaterial()->getRawContent())) {
            throw new ilQtiException('cannot import image without content');
        }
        
        $this->setDetectedMimeType(
            $this->determineMimeType($this->getImageMaterial()->getRawContent())
        );
    }
    
    /**
     * @return ilQTIMatimage
     */
    public function getImageMaterial()
    {
        return $this->imageMaterial;
    }
    
    /**
     * @param ilQTIMatimage $imageMaterial
     */
    public function setImageMaterial($imageMaterial)
    {
        $this->imageMaterial = $imageMaterial;
    }
    
    /**
     * @return string
     */
    protected function getDetectedMimeType()
    {
        return $this->detectedMimeType;
    }
    
    /**
     * @param string $detectedMimeType
     */
    protected function setDetectedMimeType($detectedMimeType)
    {
        $this->detectedMimeType = $detectedMimeType;
    }
    
    public function validate()
    {
        if (!$this->validateLabel()) {
            return false;
        }
        
        if (!$this->validateContent()) {
            return false;
        }
        
        return true;
    }
    
    protected function validateContent()
    {
        if ($this->getImageMaterial()->getImagetype() && !assQuestion::isAllowedImageMimeType($this->getImageMaterial()->getImagetype())) {
            return false;
        }

        if (!assQuestion::isAllowedImageMimeType($this->getDetectedMimeType())) {
            return false;
        }

        if ($this->getImageMaterial()->getImagetype()) {
            $declaredMimeType = assQuestion::fetchMimeTypeIdentifier($this->getImageMaterial()->getImagetype());
            $detectedMimeType = assQuestion::fetchMimeTypeIdentifier($this->getDetectedMimeType());

            if ($declaredMimeType != $detectedMimeType) {
                // since ilias exports jpeg declared pngs itself, we skip this validation ^^
                // return false;
                
                /* @var ilComponentLogger $log */
                $log = $GLOBALS['DIC'] ? $GLOBALS['DIC']['ilLog'] : $GLOBALS['ilLog'];
                $log->log(
                    'QPL: imported image with declared mime (' . $declaredMimeType . ') '
                    . 'and detected mime (' . $detectedMimeType . ')'
                );
            }
        }

        return true;
    }
    
    protected function validateLabel()
    {
        if ($this->getImageMaterial()->getUri()) {
            if (!$this->hasFileExtension($this->getImageMaterial()->getUri())) {
                return true;
            }

            $extension = $this->determineFileExtension($this->getImageMaterial()->getUri());
        } else {
            $extension = $this->determineFileExtension($this->getImageMaterial()->getLabel());
        }

        return assQuestion::isAllowedImageFileExtension($this->getDetectedMimeType(), $extension);
    }
    
    public function sanitizeLabel()
    {
        $label = $this->getImageMaterial()->getLabel();
        
        $label = basename($label);
        $label = ilUtil::stripSlashes($label);
        $label = ilUtil::getASCIIFilename($label);
        
        $this->getImageMaterial()->setLabel($label);
    }
    
    protected function determineMimeType($content)
    {
        return ilFileUtils::lookupContentMimeType($content);
    }

    /**
     * Returns the determine file extension. If no extension
     * @param string $label
     * @return string|null
     */
    protected function determineFileExtension($label)
    {
        $pathInfo = pathinfo($label);

        if (isset($pathInfo['extension'])) {
            return $pathInfo['extension'];
        }

        return null;
    }
    
    /**
     * Returns whether or not the passed label contains a file extension
     * @param string $label
     * @return bool
     */
    protected function hasFileExtension($label)
    {
        $pathInfo = pathinfo($label);

        return array_key_exists('extension', $pathInfo);
    }
}
