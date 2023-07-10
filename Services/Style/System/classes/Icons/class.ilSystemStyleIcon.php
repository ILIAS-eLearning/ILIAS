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

declare(strict_types=1);

/**
 * Abstracts an Icon and the necessary actions to get all colors out of an svg Icon
 */
class ilSystemStyleIcon
{
    /**
     * Path to the icon including name and extension
     */
    protected string $path = '';

    /**
     * Name of the Icon
     */
    protected string $name = '';

    /**
     * Extension of the icon
     */
    protected string $type = '';

    /**
     * Color set extracted from the icon
     */
    protected ilSystemStyleIconColorSet $color_set;

    public function __construct(string $name, string $path, string $type)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setPath($path);
    }

    /**
     * Changes colors in the svg file of the icon and updates the icon abstraction by extracting the colors again.
     */
    public function changeColors(array $color_changes): void
    {
        if ($this->getType() == 'svg') {
            $icon = file_get_contents($this->getPath());
            foreach ($color_changes as $old_color => $new_color) {
                $icon = preg_replace('/#' . $old_color . '/i', '#' . $new_color, $icon, -1);
            }
            file_put_contents($this->getPath(), $icon);
        }
        $this->extractColorSet();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Only get dir rel to the Customizing dir
     * without name and extension from
     */
    public function getDirRelToCustomizing(): string
    {
        $path = strstr($this->getPath(), 'global/skin');
        if (!$path) {
            return '';
        }
        return dirname($path);
    }

    public function getColorSet(): ilSystemStyleIconColorSet
    {
        if (!isset($this->color_set)) {
            $this->extractColorSet();
        }
        return $this->color_set;
    }

    /**
     * Extracts all colors from the icon by parsing the svg file for a regular expresion.
     */
    protected function extractColorSet(): void
    {
        $regex_for_extracting_color = '/((?<=#)[\dabcdef]{6})|((?<=#)[\dabcdef]{3})/i';

        $this->color_set = new ilSystemStyleIconColorSet();
        if ($this->getType() == 'svg') {
            $icon_content = file_get_contents($this->getPath());
            $color_matches = [];
            preg_match_all($regex_for_extracting_color, $icon_content, $color_matches);
            if (is_array($color_matches) && is_array($color_matches[0])) {
                foreach ($color_matches[0] as $color_value) {
                    $numeric = strtoupper(str_replace('#', '', $color_value));
                    $color = new ilSystemStyleIconColor('id_' . $numeric, $color_value, $numeric, $color_value);
                    $this->getColorSet()->addColor($color);
                }
            }
        }
    }

    public function setColorSet(ilSystemStyleIconColorSet $color_set): void
    {
        $this->color_set = $color_set;
    }

    public function usesColor(string $color_id): bool
    {
        return $this->getColorSet()->doesColorExist($color_id);
    }
}
