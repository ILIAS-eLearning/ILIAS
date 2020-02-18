<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/UIComponent/Button/classes/class.ilButton.php";

/**
 * Link Button GUI
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilTabsGUI.php 45109 2013-09-30 15:46:28Z akill $
 * @package ServicesUIComponent
 */
class ilJsLinkButton extends ilButton
{
    protected $target;

    public static function getInstance()
    {
        return new self(self::TYPE_LINK);
    }

    /**
     * Set target
     * @param string $a_value
     */
    public function setTarget($a_value)
    {
        $this->target = trim($a_value);
    }

    /**
     * Get target
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Prepare caption for render
     * @return string
     */
    protected function renderCaption()
    {
        return '&nbsp;' . $this->getCaption() . '&nbsp;';
    }

    public function render()
    {
        $this->prepareRender();

        $attr = array();

        $attr["target"]  = $this->getTarget();
        $attr["name"]    = $this->getName();
        $attr["onclick"] = $this->getOnClick();

        return '<a' . $this->renderAttributes($attr) . '>' .
        $this->renderCaption() . '</a>';
    }
}
