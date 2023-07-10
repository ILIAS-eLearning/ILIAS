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

class ilSkinStyle
{
    /**
     * Id of the skin. Currently css and less files are named accordingely
     */
    protected string $id = '';

    /**
     * Name of the style visible in all UI elements
     */
    protected string $name = '';

    /**
     * Directory to store sound files into
     */
    protected string $sound_directory = '';

    /**
     * Directory to store image files into
     */
    protected string $image_directory = '';

    /**
     * Directory to store fonts into
     */
    protected string $font_directory = '';

    /**
     * Css file name of the skin
     */
    protected string $css_file = '';

    /**
     * Parent of the skin if set
     */
    protected string $substyle_of = '';

    public function __construct(
        string $id,
        string $name,
        string $css_file = '',
        string $image_directory = '',
        string $font_directory = '',
        string $sound_directory = '',
        string $parent_style = ''
    ) {
        $this->setId($id);
        $this->setName($name);

        if ($css_file == '') {
            $css_file = $this->getId();
        }

        if ($image_directory == '') {
            $image_directory = 'images';
        }

        if ($font_directory == '') {
            $font_directory = 'fonts';
        }

        if ($sound_directory == '') {
            $sound_directory = 'sound';
        }

        $this->setCssFile($css_file);
        $this->setImageDirectory($image_directory);
        $this->setFontDirectory($font_directory);
        $this->setSoundDirectory($sound_directory);
        $this->setSubstyleOf($parent_style);
    }

    /**
     * @throws ilSystemStyleException
     */
    public static function parseFromXMLElement(SimpleXMLElement $xml_element): ilSkinStyle
    {
        return new self(
            (string) $xml_element->attributes()['id'],
            (string) $xml_element->attributes()['name'],
            (string) $xml_element->attributes()['css_file'],
            (string) $xml_element->attributes()['image_directory'],
            (string) $xml_element->attributes()['font_directory'],
            (string) $xml_element->attributes()['sound_directory']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @throws ilSystemStyleException
     */
    public function setId(string $id): void
    {
        if (strpos($id, ' ') !== false) {
            throw new ilSystemStyleException(ilSystemStyleException::INVALID_CHARACTERS_IN_ID, $id);
        }
        $this->id = str_replace(' ', '_', $id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSoundDirectory(): string
    {
        return $this->sound_directory;
    }

    public function setSoundDirectory(string $sound_directory): void
    {
        $this->sound_directory = $sound_directory;
    }

    public function getImageDirectory(): string
    {
        return $this->image_directory;
    }

    public function setImageDirectory(string $image_directory): void
    {
        $this->image_directory = $image_directory;
    }

    public function getCssFile(): string
    {
        return $this->css_file;
    }

    public function setCssFile(string $css_file): void
    {
        $this->css_file = $css_file;
    }

    public function getFontDirectory(): string
    {
        return $this->font_directory;
    }

    public function setFontDirectory(string $font_directory): void
    {
        $this->font_directory = $font_directory;
    }

    /**
     * Returns the parent style of this style if set
     */
    public function getSubstyleOf(): string
    {
        return $this->substyle_of;
    }

    /**
     * Sets style as sub style of another
     */
    public function setSubstyleOf(string $substyle_of): void
    {
        $this->substyle_of = $substyle_of;
    }

    /**
     * Return wheter this style is a substyle of another
     */
    public function isSubstyle(): bool
    {
        return $this->getSubstyleOf() != '';
    }

    /**
     * Checks if a resource (folder) relative to the style is referenced by this style. Used to decide if folder can be deleted.
     */
    public function referencesResource(string $resource): bool
    {
        return $this->getCssFile() == $resource
            || $this->getImageDirectory() == $resource
            || $this->getFontDirectory() == $resource
            || $this->getSoundDirectory() == $resource;
    }
}
