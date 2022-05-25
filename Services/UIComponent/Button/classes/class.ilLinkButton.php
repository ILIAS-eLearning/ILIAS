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

/**
 * Link Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @deprecated use KS Buttons instead
 */
class ilLinkButton extends ilButtonBase
{
    protected string $url = "";
    protected string $target = "";
    
    public static function getInstance() : self
    {
        return new self(self::TYPE_LINK);
    }
    
    
    //
    // properties
    //
    
    public function setUrl(string $a_value) : void
    {
        $this->url = trim($a_value);
    }
    
    public function getUrl() : string
    {
        return $this->url;
    }
    
    public function setTarget(string $a_value) : void
    {
        $this->target = trim($a_value);
    }
    
    public function getTarget() : string
    {
        return $this->target;
    }
    
    
    //
    // render
    //
    
    protected function renderCaption() : string
    {
        return '&nbsp;' . $this->getCaption() . '&nbsp;';
    }

    protected function renderAttributes(array $a_additional_attr = null) : string
    {
        if ('_blank' === $this->getTarget()) {
            $relAttrVal = 'noopener';

            if (isset($a_additional_attr['rel'])) {
                if (strpos($a_additional_attr['rel'], $relAttrVal) === false) {
                    $a_additional_attr['rel'] .= ' ' . $relAttrVal;
                }
            } else {
                $a_additional_attr['rel'] = $relAttrVal;
            }
        }

        return parent::renderAttributes($a_additional_attr);
    }

    public function render() : string
    {
        $this->prepareRender();
        
        $attr = array();
        $attr["href"] = $this->getUrl() ?: "#";
        $attr["target"] = $this->getTarget();
        
        return '<a' . $this->renderAttributes($attr) . '>' .
            $this->renderCaption() . '</a>';
    }
}
