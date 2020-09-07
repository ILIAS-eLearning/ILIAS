<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Progress bar GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilTabsGUI.php 45109 2013-09-30 15:46:28Z akill $
 * @package ServicesUIComponent
 */
class ilProgressBar
{
    protected $min; // [int]
    protected $max; // [int]
    protected $current; // [int]
    protected $show_caption; // [bool]
    protected $type; // [int]
    protected $caption; // [text]
    protected $striped; // [bool]
    protected $animated; // [bool]
    
    
    protected $ajax_url = '';
    protected $ajax_timeout = 5;
    protected $unique_id = '';
    
    const TYPE_INFO = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_WARNING = 3;
    const TYPE_DANGER = 4;
    
    /**
     * Constructor
     *
     * @return self
     */
    protected function __construct()
    {
        $this->setMin(0);
        $this->setMax(100);
        $this->setShowCaption(true);
        $this->setType(self::TYPE_INFO);
        $this->setStriped(true);
    }
    
    /**
     * Factory
     *
     * @return ilProgressBar
     */
    public static function getInstance()
    {
        return new self();
    }
    
    
    //
    // properties
    //
    
    /**
     * Set type (currently unwanted)
     *
     * @param int $a_value
     */
    public function setType($a_value)
    {
        $valid = array(
            self::TYPE_INFO
            ,self::TYPE_SUCCESS
            ,self::TYPE_WARNING
            ,self::TYPE_DANGER
        );
        if (in_array($a_value, $valid)) {
            $this->type = $a_value;
        }
    }
    
    /**
     * Set minimum value
     *
     * @param int $a_value
     */
    public function setMin($a_value)
    {
        $this->min = abs((int) $a_value);
    }
    
    /**
     * Set maximum value
     *
     * @param int $a_value
     */
    public function setMax($a_value)
    {
        $this->max = abs((int) $a_value);
    }
    
    /**
     * Set Caption
     *
     * @param string $a_value
     */
    public function setCaption($a_value)
    {
        $this->caption = trim($a_value);
    }
    
    /**
     * Toggle show caption status
     *
     * @param bool $a_value
     */
    public function setShowCaption($a_value)
    {
        $this->show_caption = (bool) $a_value;
    }
    
    /**
     * Toggle striped layout
     *
     * @param bool $a_value
     */
    public function setStriped($a_value)
    {
        $this->striped = (bool) $a_value;
    }
    
    /**
     * Toggle animated layout
     *
     * @param bool $a_value
     */
    public function setAnimated($a_value)
    {
        $this->animated = (bool) $a_value;
    }
    
    /**
     * Set current value
     *
     * @param int|float $a_value
     */
    public function setCurrent($a_value)
    {
        $this->current = abs($a_value);
    }
    
    public function setAsyncStatusUrl($a_target)
    {
        $this->ajax_url = $a_target;
    }
    
    public function setAsynStatusTimeout($a_timeout)
    {
        $this->async_timeout = $a_timeout;
    }
    
    public function setId($a_id)
    {
        $this->unique_id = $a_id;
    }
    
    //
    // presentation
    //
    
    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $tpl = new ilTemplate("tpl.il_progress.html", true, true, "Services/UIComponent/ProgressBar");
        
        $tpl->setVariable("MIN", $this->min);
        $tpl->setVariable("MAX", $this->max);
        $tpl->setVariable("CURRENT_INT", round($this->current));
        $tpl->setVariable("CURRENT", round($this->current));
        $tpl->setVariable("CAPTION", $this->caption);
        
        $map = array(
            self::TYPE_INFO => "info"
            ,self::TYPE_SUCCESS => "success"
            ,self::TYPE_WARNING => "warning"
            ,self::TYPE_DANGER => "danger"
        );
        $css = array("progress-bar-" . $map[$this->type]);
        
        if ($this->striped) {
            $css[] = "progress-bar-striped";
        }
        
        if ($this->animated) {
            $css[] = "active";
        }
        
        $tpl->setVariable("CSS", implode(" ", $css));
        
        if (!$this->show_caption) {
            $tpl->touchBlock("hide_caption_in_bl");
            $tpl->touchBlock("hide_caption_out_bl");
        }
        
        if (strlen($this->ajax_url) and $this->ajax_timeout) {
            $tpl->setCurrentBlock('async_status');
            $tpl->setVariable('ASYNC_STATUS_ID', $this->unique_id);
            $tpl->setVariable('ICON_OK', ilUtil::getImagePath('icon_ok.svg'));
            $tpl->setVariable('AJAX_URL', $this->ajax_url);
            $tpl->setVariable('AJAX_TIMEOUT', 1000 * (int) $this->ajax_timeout);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('PROGRESS_ID', $this->unique_id);
        
        return $tpl->get();
    }
}
