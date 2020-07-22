<?php
/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinStyleXML
{
    /**
     * Id of the skin. Currently css and less files are named accordingely
     * @var string
     */
    protected $id = "";

    /**
     * Name of the style visible in all UI elements
     *
     * @var string
     */
    protected $name = "";

    /**
     * Directory to store sound files into
     *
     * @var string
     */
    protected $sound_directory = "";

    /**
     * Directory to store image files into
     *
     * @var string
     */
    protected $image_directory = "";

    /**
     * Directory to store fonts into
     *
     * @var string
     */
    protected $font_directory = "";

    /**
     * Css file name of the skin
     *
     * @var string
     */
    protected $css_file = "";

    /**
     * Parent of the skin if set
     *
     * @var string
     */
    protected $substyle_of = "";

    /**
     * ilSkinStyleXML constructor.
     * @param $id
     * @param $name
     * @param string $css_file
     * @param string $image_directory
     * @param string $font_directory
     * @param string $sound_directory
     * @param string $parent_style
     * @throws ilSystemStyleException
     */
    public function __construct($id, $name, $css_file = "", $image_directory = "", $font_directory = "", $sound_directory = "", $parent_style = "")
    {
        $this->setId($id);
        $this->setName($name);

        if ($css_file == "") {
            $css_file = $this->getId();
        }

        if ($image_directory == "") {
            $image_directory = "images";
        }

        if ($font_directory == "") {
            $font_directory = "fonts";
        }

        if ($sound_directory == "") {
            $sound_directory = "sound";
        }

        $this->setCssFile($css_file);
        $this->setImageDirectory($image_directory);
        $this->setFontDirectory($font_directory);
        $this->setSoundDirectory($sound_directory);
        $this->setSubstyleOf($parent_style);
    }

    /**
     * @param SimpleXMLElement $xml_element
     * @return ilSkinStyleXML
     * @throws ilSystemStyleException
     */
    public static function parseFromXMLElement(SimpleXMLElement $xml_element)
    {
        $style = new self(
            (string) $xml_element->attributes()["id"],
            (string) $xml_element->attributes()["name"],
            (string) $xml_element->attributes()["css_file"],
            (string) $xml_element->attributes()["image_directory"],
            (string) $xml_element->attributes()["font_directory"],
            (string) $xml_element->attributes()["sound_directory"]
        );
        return $style;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @throws ilSystemStyleException
     */
    public function setId($id)
    {
        if (strpos($id, ' ') !== false) {
            throw new ilSystemStyleException(ilSystemStyleException::INVALID_CHARACTERS_IN_ID, $id);
        }
        $this->id = str_replace(" ", "_", $id);
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
     * @return string
     */
    public function getSoundDirectory()
    {
        return $this->sound_directory;
    }

    /**
     * @param string $sound_directory
     */
    public function setSoundDirectory($sound_directory)
    {
        $this->sound_directory = $sound_directory;
    }

    /**
     * @return string
     */
    public function getImageDirectory()
    {
        return $this->image_directory;
    }

    /**
     * @param string $image_directory
     */
    public function setImageDirectory($image_directory)
    {
        $this->image_directory = $image_directory;
    }

    /**
     * @return string
     */
    public function getCssFile()
    {
        return $this->css_file;
    }

    /**
     * @param string $css_file
     */
    public function setCssFile($css_file)
    {
        $this->css_file = $css_file;
    }

    /**
     * @return string
     */
    public function getFontDirectory()
    {
        return $this->font_directory;
    }

    /**
     * @param string $font_directory
     */
    public function setFontDirectory($font_directory)
    {
        $this->font_directory = $font_directory;
    }

    /**
     * Returns the parent style of this style if set
     *
     * @return string
     */
    public function getSubstyleOf()
    {
        return $this->substyle_of;
    }

    /**
     * Sets style as sub style of another
     *
     * @param string $substyle_of
     */
    public function setSubstyleOf($substyle_of)
    {
        $this->substyle_of = $substyle_of;
    }

    /**
     * Return wheter this style is a substyle of another
     *
     * @return bool
     */
    public function isSubstyle()
    {
        return $this->getSubstyleOf() != "";
    }

    /**
     * Checks if a resource (folder) relative to the style is referenced by this style. Used to decide if folder can be deleted.
     *
     * @param $resource
     * @return bool
     */
    public function referencesResource($resource)
    {
        return $this->getCssFile() == $resource
            || $this->getImageDirectory() == $resource
            || $this->getFontDirectory() == $resource
            || $this->getSoundDirectory() == $resource;
    }
}
