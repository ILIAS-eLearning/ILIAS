<?php

declare(strict_types=1);

/**
 * Abstracts a folder containing a set of icons.
 */
class ilSystemStyleIconFolder
{
    /**
     * Icons a folder contains
     * @var ilSystemStyleIcon[]
     */
    protected array $icons = [];

    /**
     * Path to the root of the folder
     */
    protected string $path = '';

    /**
     * Complete color set of all icons contained in this folder
     */
    protected ilSystemStyleIconColorSet $color_set;

    /**
     * ilSystemStyleIconFolder constructor.
     */
    public function __construct(string $path)
    {
        $this->setPath($path);
        $this->read();
    }

    /**
     * Reads the folder recursively and sorts the icons by name and type
     * @throws ilSystemStyleException
     */
    public function read() : void
    {
        $this->readIconsFromFolder($this->getPath());
        $this->sortIcons();
    }

    /**
     * Sorts the Icons by name and type
     */
    protected function sortIcons() : void
    {
        usort($this->icons, [$this, 'compareIconsByName']);
    }

    protected function compareIconsByName(ilSystemStyleIcon $a, ilSystemStyleIcon $b) : int
    {
        if ($a->getType() == $b->getType()) {
            return strcmp($a->getName(), $b->getName());
        } elseif ($a->getType() == 'svg') {
            return -1;
        } elseif ($b->getType() == 'svg') {
            return 1;
        } else {
            return strcmp($a->getType(), $b->getType());
        }
    }

    public function sortIconsByPath() : void
    {
        usort($this->icons, static function (ilSystemStyleIcon $a, ilSystemStyleIcon $b) : int {
            return strcmp($a->getPath(), $b->getPath());
        });
    }

    /**
     * @throws ilSystemStyleException
     * @throws ilSystemStyleIconException
     */
    protected function readIconsFromFolder(string $src = '') : void
    {
        try {
            $dir_iterator = new RecursiveDirectoryIterator($src);
        } catch (UnexpectedValueException $e) {
            throw new ilSystemStyleIconException(ilSystemStyleIconException::IMAGES_FOLDER_DOES_NOT_EXIST, $src);
        }

        $rec_it = new RecursiveIteratorIterator($dir_iterator);

        foreach ($rec_it as $file) {
            if (!$file->isReadable()) {
                throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $file->getPathname());
            }
            if ($file->isFile()) {
                $extension = $file->getExtension();
                if ($extension == 'gif' || $extension == 'svg' || $extension == 'png') {
                    $this->addIcon(new ilSystemStyleIcon($file->getFilename(), $file->getPathname(), $extension));
                }
            }
        }
    }

    /**
     * Changes a set of colors in all icons contained in the folder
     */
    public function changeIconColors(array $color_changes) : void
    {
        foreach ($this->getIcons() as $icon) {
            $icon->changeColors($color_changes);
        }
    }

    /**
     * Adds an icon to the folders abstraction
     */
    public function addIcon(ilSystemStyleIcon $icon) : void
    {
        $this->icons[] = $icon;
    }

    /**
     * Gets an Icon from the folders abstraction
     * @return ilSystemStyleIcon[]
     */
    public function getIcons() : array
    {
        return $this->icons;
    }

    /**
     * @throws ilSystemStyleIconException
     */
    public function getIconByName(string $name) : ilSystemStyleIcon
    {
        foreach ($this->icons as $icon) {
            if ($icon->getName() == $name) {
                return $icon;
            }
        }
        throw new ilSystemStyleIconException(ilSystemStyleIconException::ICON_DOES_NOT_EXIST, $name);
    }

    /**
     * @throws ilSystemStyleIconException
     */
    public function getIconByPath(string $path) : ilSystemStyleIcon
    {
        foreach ($this->icons as $icon) {
            if ($icon->getPath() == $path) {
                return $icon;
            }
        }
        throw new ilSystemStyleIconException(ilSystemStyleIconException::ICON_DOES_NOT_EXIST, $path);
    }

    /**
     * Sorts all icons by their occurrence in folders
     * @return array array(folder_path_name => [$icons])
     */
    public function getIconsSortedByFolder() : array
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
    public function setIcons(array $icons)
    {
        $this->icons = $icons;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getColorSet() : ilSystemStyleIconColorSet
    {
        if (!isset($this->color_set)) {
            $this->extractColorSet();
        }
        return $this->color_set;
    }

    /**
     * Gets the color sets of all icons an merges them into one
     */
    protected function extractColorSet() : void
    {
        $this->color_set = new ilSystemStyleIconColorSet();
        foreach ($this->getIcons() as $icon) {
            $this->color_set->mergeColorSet($icon->getColorSet());
        }
    }

    /**
     * Gets the usages of a certain color
     * @return ilSystemStyleIcon[]
     */
    public function getUsagesOfColor(string $color_id) : array
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
     */
    public function getUsagesOfColorAsString(string $color_id) : string
    {
        $usage_string = '';
        foreach ($this->getUsagesOfColor($color_id) as $icon) {
            $usage_string .= rtrim($icon->getName(), '.svg') . '; ';
        }
        return $usage_string;
    }

    public function setColorSet(ilSystemStyleIconColorSet $color_set) : void
    {
        $this->color_set = $color_set;
    }
}
