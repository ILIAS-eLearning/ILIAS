<?php
/**
 * 
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilDataCollectionLinkButton extends ilLinkButton {
    const TYPE_DATACOLLECTION_LINK = 99;

    protected $attributes;

    protected $useWrapper = false;

    /**
     * @return boolean
     */
    public function isUseWrapper()
    {
        return $this->useWrapper;
    }

    /**
     * @param boolean $useWrapper
     */
    public function setUseWrapper($useWrapper)
    {
        $this->useWrapper = $useWrapper;
    }

    public static function getInstance()
    {
        return new self(self::TYPE_DATACOLLECTION_LINK);
    }

    public function prepareRender() {
        parent::prepareRender();

        $this->addAttribute('href', ($this->getUrl() ? $this->getUrl() : "#"));
        $this->addAttribute('target', $this->getTarget());
    }

    public function render() {
        $this->prepareRender();

        $output = '';
        if($this->useWrapper) {
            $output .= '<div'.$this->renderAttributesHelper($this->attributes['wrapper']).'>';
        }

        $output .= '<a'.$this->renderAttributes($this->attributes['default']).'>'.$this->renderCaption().'</a>';

        if($this->useWrapper) {
            $output .= '</div>';
        }
        return $output;
    }

    public function addAttribute($key, $value, $wrapper = false) {
            $this->attributes[$this->getGroupKey($wrapper)][$key] = $value;
    }

    public function removeAttribute($key, $wrapper = false) {
        if(isset($this->attributes[$this->getGroupKey($wrapper)][$key])) {
            unset($this->attributes[$this->getGroupKey($wrapper)][$key]);
            return true;
        }
        return false;
    }

    public function getAttribute($key, $wrapper = false) {
        if(isset($this->attributes[$this->getGroupKey($wrapper)][$key])) {
            return $this->attributes[$this->getGroupKey($wrapper)][$key];
        }
        return null;
    }

    protected function getGroupKey($wrapper) {
        return ($wrapper)? 'wrapper' : 'default';
    }
}