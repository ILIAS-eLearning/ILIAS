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
 ********************************************************************
 */

/**
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilDclLinkButton extends ilLinkButton
{
    const TYPE_DATACOLLECTION_LINK = 99;
    protected array $attributes;
    protected bool $useWrapper = false;

    public function isUseWrapper() : bool
    {
        return $this->useWrapper;
    }

    public function setUseWrapper(bool $useWrapper) : void
    {
        $this->useWrapper = $useWrapper;
    }

    public static function getInstance() : self
    {
        return new self(self::TYPE_DATACOLLECTION_LINK);
    }

    protected function prepareRender() : void
    {
        parent::prepareRender();

        $this->addAttribute('href', ($this->getUrl() ?: "#"));
        $this->addAttribute('target', $this->getTarget());
    }

    public function render() : string
    {
        $this->prepareRender();

        $output = '';
        if ($this->useWrapper) {
            $output .= '<div' . $this->renderAttributesHelper($this->attributes['wrapper']) . '>';
        }

        $output .= '<a' . $this->renderAttributes($this->attributes['default']) . '>' . $this->renderCaption() . '</a>';

        if ($this->useWrapper) {
            $output .= '</div>';
        }

        return $output;
    }

    public function addAttribute(string $key, string $value, bool $wrapper = false) : void
    {
        $this->attributes[$this->getGroupKey($wrapper)][$key] = $value;
    }

    public function removeAttribute(string $key, $wrapper = false) : bool
    {
        if (isset($this->attributes[$this->getGroupKey($wrapper)][$key])) {
            unset($this->attributes[$this->getGroupKey($wrapper)][$key]);

            return true;
        }

        return false;
    }

    public function getAttribute(string $key, bool $wrapper = false) : ?string
    {
        if (isset($this->attributes[$this->getGroupKey($wrapper)][$key])) {
            return $this->attributes[$this->getGroupKey($wrapper)][$key];
        }

        return null;
    }

    protected function getGroupKey($wrapper) : string
    {
        return ($wrapper) ? 'wrapper' : 'default';
    }
}
