<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/UIComponent/Button/classes/class.ilButtonBase.php";

/**
 * Link Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilTabsGUI.php 45109 2013-09-30 15:46:28Z akill $
 * @package ServicesUIComponent
 */
class ilLinkButton extends ilButtonBase
{
    protected $url; // [string]
    protected $target; // [string]
    
    public static function getInstance()
    {
        return new self(self::TYPE_LINK);
    }
    
    
    //
    // properties
    //
    
    /**
     * Set URL
     *
     * @param string $a_value
     */
    public function setUrl($a_value)
    {
        $this->url = trim($a_value);
    }
    
    /**
     * Get URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Set target
     *
     * @param string $a_value
     */
    public function setTarget($a_value)
    {
        $this->target = trim($a_value);
    }
    
    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }
    
    
    //
    // render
    //
    
    /**
     * Prepare caption for render
     *
     * @return string
     */
    protected function renderCaption()
    {
        return '&nbsp;' . $this->getCaption() . '&nbsp;';
    }

    /**
     * @inheritdoc
     */
    protected function renderAttributes(array $a_additional_attr = null)
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

    public function render()
    {
        $this->prepareRender();
        
        $attr = array();
        $attr["href"] = $this->getUrl() ? $this->getUrl() : "#";
        $attr["target"] = $this->getTarget();
        
        return '<a' . $this->renderAttributes($attr) . '>' .
            $this->renderCaption() . '</a>';
    }
}
