<?php
include_once('./Customizing/global/plugins/Libraries/ActiveRecord/Fields/class.arField.php');
/**
 * GUI-Class arViewField
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.6
 *
 */
class arViewField extends arField
{
    /**
     * @var string
     */
    protected $txt = "";
    /**
     * @var int
     */
    protected $position = 1000;
    /**
     * @var string
     */
    protected $visible = "";


    /**
     * @param $name
     * @param null $txt
     * @param null $type
     * @param null $position
     * @param bool $visible
     */
    function __construct($name, $txt = null, $type = null, $position = 1000, $visible = true)
    {
        $this->name     = $name;
        $this->position = $position;
        $this->txt      = $txt;
        $this->type     = $type;
        $this->visible  = $visible;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $txt
     */
    public function setTxt($txt)
    {
        $this->txt = $txt;
    }

    /**
     * @return string
     */
    public function getTxt()
    {
        return $this->txt;
    }

    /**
     * @param string $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return string
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param arField $field
     * @return arViewField
     */
    static function castFromFieldToViewField(arField $field)
    {
        require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableField.php');
        require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditField.php');
        require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Display/class.arDisplayField.php');
        $new_class = get_called_class();
        $obj = new $new_class();
        foreach (get_object_vars($field) as $key => $name)
        {
            $obj->$key = $name;
        }
        return $obj;
    }
}

?>