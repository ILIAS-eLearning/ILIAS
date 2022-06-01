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
 ********************************************************************
 */

/**
* QTI matimage class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMatimage
{
    public const EMBEDDED_BASE64 = 'base64';

    public ?string $imagetype = null;
    public string $label = '';
    public ?string $height = null;
    public ?string $width = null;
    public string $uri = '';
    public ?string $embedded = null;
    public ?string $x0 = null;
    public ?string $y0 = null;
    public ?string $entityref = null;
    public string $content = '';

    public function setImagetype(string $a_imagetype) : void
    {
        $this->imagetype = $a_imagetype;
    }

    public function getImagetype() : ?string
    {
        return $this->imagetype;
    }

    public function setLabel(string $a_label) : void
    {
        $this->label = $a_label;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function setHeight(string $a_height) : void
    {
        $this->height = $a_height;
    }

    public function getHeight() : ?string
    {
        return $this->height;
    }

    public function setWidth(string $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getWidth() : ?string
    {
        return $this->width;
    }

    public function setEmbedded(string $a_embedded) : void
    {
        $this->embedded = $a_embedded;
    }

    public function getEmbedded() : ?string
    {
        return $this->embedded;
    }

    public function setUri(string $a_uri) : void
    {
        $this->uri = $a_uri;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
    
    public function setX0(string $a_x0) : void
    {
        $this->x0 = $a_x0;
    }
    
    public function getX0() : ?string
    {
        return $this->x0;
    }
    
    public function setY0(string $a_y0) : void
    {
        $this->y0 = $a_y0;
    }
    
    public function getY0() : ?string
    {
        return $this->y0;
    }

    public function setEntityref(string $a_entityref) : void
    {
        $this->entityref = $a_entityref;
    }

    public function getEntityref() : ?string
    {
        return $this->entityref;
    }

    public function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @return string|null|false
     */
    public function getRawContent()
    {
        switch ($this->getEmbedded()) {
            case self::EMBEDDED_BASE64:
                
                return base64_decode($this->getContent());
        }
        
        return $this->getContent();
    }
}
