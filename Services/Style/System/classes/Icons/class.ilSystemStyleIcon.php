<?php
include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIconColorSet.php");


/**
 * Abstracts an Icon and the necessary actions to get all colors out of an svg Icon
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleIcon
{

    /**
     * Path to the icon including name and extension
     * @var string
     */
    protected $path = "";

    /**
     * Name of the Icon
     * @var string
     */
    protected $name = "";

    /**
     * Extension of the icon
     * @var string
     */
    protected $type = "";

    /**
     * Color set extracted from the icon
     *
     * @var ilSystemStyleIconColorSet
     */
    protected $color_set = null;

    /**
     * ilSystemStyleIcon constructor.
     * @param $name
     * @param $path
     * @param $type
     */
    public function __construct($name, $path, $type)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setPath($path);
    }


    /**
     * Changes colors in the svg file of the icon and updates the icon abstraction by extracting the colors again.
     * @param array $color_changes
     */
    public function changeColors(array $color_changes)
    {
        if ($this->getType() == "svg") {
            $icon = file_get_contents($this->getPath());
            foreach ($color_changes as $old_color => $new_color) {
                $icon = preg_replace('/' . $old_color . '/i', $new_color, $icon, -1);
            }
            file_put_contents($this->getPath(), $icon);
        }
        $this->extractColorSet();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Only get dir rel to the Customizing dir
     * without name and extension from
     * @return string
     */
    public function getDirRelToCustomizing()
    {
        return dirname(strstr($this->getPath(), 'global/skin'));
    }

    /**
     * @return ilSystemStyleIconColorSet
     */
    public function getColorSet()
    {
        if (!$this->color_set) {
            $this->extractColorSet();
        }
        return $this->color_set;
    }

    /**
     * Extracts all colors from the icon by parsing the svg file for a regular expresion.
     */
    protected function extractColorSet()
    {
        $regex_for_extracting_color = '/(?<=#)[\dabcdef]{6}/i';

        $this->color_set = new ilSystemStyleIconColorSet();
        if ($this->getType() == "svg") {
            $icon_content = file_get_contents($this->getPath());
            $color_matches = [];
            preg_match_all($regex_for_extracting_color, $icon_content, $color_matches);
            if (is_array($color_matches) && is_array($color_matches[0])) {
                foreach ($color_matches[0] as $color_value) {
                    $numeric = strtoupper(str_replace("#", "", $color_value));
                    $color = new ilSystemStyleIconColor($numeric, $color_value, $numeric, $color_value);
                    $this->getColorSet()->addColor($color);
                }
            }
        }
    }

    /**
     * @param ilSystemStyleIconColorSet $color_set
     */
    public function setColorSet($color_set)
    {
        $this->color_set = $color_set;
    }

    /**
     * @param $color_id
     * @return bool
     */
    public function usesColor($color_id)
    {
        return $this->getColorSet()->doesColorExist($color_id);
    }
}
