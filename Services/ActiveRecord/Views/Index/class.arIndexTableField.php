<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewField.php');
/**
 * GUI-Class arViewField
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.6
 *
 */
class arIndexTableField extends arViewField
{
    /**
     * @var bool
     */
    protected $has_filter = false;
    /**
     * @var bool
     */
    protected $sortable = false;


    /**
     * @param $name
     * @param null $txt
     * @param null $type
     * @param null $position
     * @param bool $visible
     * @param bool $sortable
     * @param bool $has_filter
     */
    function __construct($name = "" , $txt = null, $type = null, $position = null, $visible = false, $sortable = false, $has_filter = false)
    {
        $this->sortable = $sortable;
        $this->has_filter  = $has_filter;
        parent::__construct($name, $txt, $type, $position, $visible);
    }

    /**
     * @param boolean $has_filter
     */
    public function setHasFilter($has_filter)
    {
        $this->has_filter = $has_filter;
    }

    /**
     * @return boolean
     */
    public function getHasFilter()
    {
        return $this->has_filter;
    }

    /**
     * @param boolean $sortable
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
    }

    /**
     * @return boolean
     */
    public function getSortable()
    {
        return $this->sortable;
    }


}

?>