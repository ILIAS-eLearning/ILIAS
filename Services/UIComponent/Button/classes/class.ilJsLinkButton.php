<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Link Button GUI
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
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

        $attr["target"] = $this->getTarget();
        $attr["name"] = $this->getName();
        $attr["onclick"] = $this->getOnClick();

        return '<a' . $this->renderAttributes($attr) . '>' .
        $this->renderCaption() . '</a>';
    }
}
