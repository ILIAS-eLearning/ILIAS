<?php
require_once("./Services/Style/System/classes/Icons/class.ilSystemStyleIcon.php");
require_once("./Services/Style/System/classes/Exceptions/class.ilSystemStyleIconException.php");

/**
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 */

/**
 * Abstracts a folder containing a set of icons.
 *
 * Class ilSystemStyleIconFolder
 */
class ilSystemStyleIconFolder
{
    /**
     * Icons a folder contains
     *
     * @var ilSystemStyleIcon[]
     */
    protected $icons = [];

    /**
     * Path to the root of the folder
     *
     * @var string
     */
    protected $path = "";

    /**
     * Complete color set of all icons contained in this folder
     *
     * @var ilSystemStyleIconColorSet
     */
    protected $color_set = null;

    /**
     * ilSystemStyleIconFolder constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setPath($path);
        $this->read();
    }

    /**
     * Reads the folder recursively and sorts the icons by name and type
     *
     * @throws ilSystemStyleException
     */
    public function read()
    {
        $this->xRead($this->getPath(), "");
        $this->sortIcons();
    }

    /**
     * Sorts the Icons by name and type
     */
    protected function sortIcons()
    {
        usort($this->icons, function (ilSystemStyleIcon $a, ilSystemStyleIcon $b) {
            if ($a->getType() == $b->getType()) {
                return strcmp($a->getName(), $b->getName());
            } else {
                if ($a->getType() == "svg") {
                    return false;
                } elseif ($b->getType() == "svg") {
                    return true;
                } else {
                    return strcmp($a->getType(), $b->getType());
                }
            }
        });
    }

    /**
     * @param string $src
     * @param string $rel_path
     * @throws ilSystemStyleException
     * @throws ilSystemStyleIconException
     */
    protected function xRead($src = "", $rel_path="")
    {
        if (!is_dir($src)) {
            throw new ilSystemStyleIconException(ilSystemStyleIconException::IMAGES_FOLDER_DOES_NOT_EXIST, $src);
        }
        foreach (scandir($src) as $file) {
            $src_file = rtrim($src, '/') . '/' . $file;
            if (!is_readable($src_file)) {
                throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $src_file);
            }
            if (substr($file, 0, 1) != ".") {
                if (is_dir($src_file)) {
                    self::xRead($src_file, $rel_path . "/" . $file);
                } else {
                    $info = new SplFileInfo($src_file);
                    $extension = $info->getExtension();
                    if ($extension == "gif" || $extension == "svg" || $extension == "png") {
                        $this->addIcon(new ilSystemStyleIcon($file, $this->getPath() . $rel_path . "/" . $file, $extension));
                    }
                }
            }
        }
    }


    /**
     * Changes a set of colors in all icons contained in the folder
     *
     * @param array $color_changes
     */
    public function changeIconColors(array $color_changes)
    {
        foreach ($this->getIcons() as $icon) {
            $icon->changeColors($color_changes);
        }
    }

    /**
     * Adds an icon to the folders abstraction
     *
     * @param ilSystemStyleIcon $icon
     */
    public function addIcon(ilSystemStyleIcon $icon)
    {
        $this->icons[] = $icon;
    }

    /**
     * Gets an Icon from the folders abstraction
     *
     * @return ilSystemStyleIcon[]
     */
    public function getIcons()
    {
        return $this->icons;
    }

    /**
     * @param $name
     * @return ilSystemStyleIcon
     * @throws ilSystemStyleIconException
     */
    public function getIconByName($name)
    {
        foreach ($this->icons as $icon) {
            if ($icon->getName() == $name) {
                return $icon;
            }
        }
        throw new ilSystemStyleIconException(ilSystemStyleIconException::ICON_DOES_NOT_EXIST, $name);
    }

    /**
     * Sorts all icons by their occurrence in folders
     *
     * @return array array(folder_path_name => [$icons])
     */
    public function getIconsSortedByFolder()
    {
        $folders = [];

        foreach ($this->getIcons() as $icon) {
            $folders[dirname($icon->getPath())][] = $icon;
        }

        ksort($folders);

        foreach ($folders as $id => $folder) {
            ksort($folders[$id]);
        }

        return $folders;
    }
    /**
     * @param ilSystemStyleIcon[] $icons
     */
    public function setIcons($icons)
    {
        $this->icons = $icons;
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
     * Gets the color sets of all icons an merges them into one
     */
    protected function extractColorSet()
    {
        $this->color_set = new ilSystemStyleIconColorSet();
        foreach ($this->getIcons() as $icon) {
            $this->color_set->mergeColorSet($icon->getColorSet());
        }
    }

    /**
     * Gets the usages of a certain color
     *
     * @param $color_id
     * @return ilSystemStyleIcon[]
     */
    public function getUsagesOfColor($color_id)
    {
        $icons = [];
        foreach ($this->getIcons() as $icon) {
            if ($icon->usesColor($color_id)) {
                $icons[] = $icon;
            }
        }
        return $icons;
    }

    /**
     * Gets the usages of a color as string
     *
     * @param $color_id
     * @return string
     */
    public function getUsagesOfColorAsString($color_id)
    {
        $usage_string = "";
        foreach ($this->getUsagesOfColor($color_id) as $icon) {
            $usage_string .= rtrim($icon->getName(), ".svg") . "; ";
        }
        return $usage_string;
    }

    /**
     * @param $color_set
     */
    public function setColorSet($color_set)
    {
        $this->color_set = $color_set;
    }
}
