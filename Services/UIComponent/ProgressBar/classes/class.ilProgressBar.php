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
 * Progress bar GUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @deprecated 10
 */
class ilProgressBar
{
    public const TYPE_INFO = 1;
    public const TYPE_SUCCESS = 2;
    public const TYPE_WARNING = 3;
    public const TYPE_DANGER = 4;

    protected int $min = 0;
    protected int $max = 0;
    protected int $current = 0;
    protected bool $show_caption = false;
    protected int $type = 0;
    protected string $caption = "";
    protected bool $striped = false;
    protected bool $animated = false;
    protected string $ajax_url = '';
    protected int $ajax_timeout = 5;
    protected string $unique_id = '';
    protected int $async_timeout = 0;

    protected function __construct()
    {
        $this->setMin(0);
        $this->setMax(100);
        $this->setShowCaption(true);
        $this->setType(self::TYPE_INFO);
        $this->setStriped(true);
    }
    
    public static function getInstance() : self
    {
        return new self();
    }
    
    public function setType(int $a_value) : void
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

    public function setMin(int $a_value) : void
    {
        $this->min = abs($a_value);
    }

    public function setMax(int $a_value) : void
    {
        $this->max = abs($a_value);
    }

    public function setCaption(string $a_value) : void
    {
        $this->caption = trim($a_value);
    }

    public function setShowCaption(bool $a_value) : void
    {
        $this->show_caption = $a_value;
    }

    public function setStriped(bool $a_value) : void
    {
        $this->striped = $a_value;
    }

    public function setAnimated(bool $a_value) : void
    {
        $this->animated = $a_value;
    }

    public function setCurrent(float $a_value) : void
    {
        $this->current = abs($a_value);
    }
    
    public function setAsyncStatusUrl(string $a_target) : void
    {
        $this->ajax_url = $a_target;
    }
    
    public function setAsynStatusTimeout(int $a_timeout) : void
    {
        $this->async_timeout = $a_timeout;
    }
    
    public function setId(string $a_id) : void
    {
        $this->unique_id = $a_id;
    }
    
    public function render() : string
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
        
        if ($this->ajax_url !== '' && $this->ajax_timeout) {
            $tpl->setCurrentBlock('async_status');
            $tpl->setVariable('ASYNC_STATUS_ID', $this->unique_id);
            $tpl->setVariable('ICON_OK', ilUtil::getImagePath('icon_ok.svg'));
            $tpl->setVariable('AJAX_URL', $this->ajax_url);
            $tpl->setVariable('AJAX_TIMEOUT', 1000 * $this->ajax_timeout);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('PROGRESS_ID', $this->unique_id);
        
        return $tpl->get();
    }
}
