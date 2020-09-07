<?php
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleException.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");

/**
 * ilSkinXml holds an manages the basic data of a skin as provide by the template of the skin. This class is also
 * responsible to read this data from the xml and, after manipulations transfer the data back to xml.
 *
 * To read a skin from xml do not use this class, us ilSkinContainer instead.
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinXML implements \Iterator, \Countable
{

    /**
     * ID of the skin, equals the name of the folder this skin is stored in
     * @var string
     */
    protected $id = "";


    /**
     * Name of the skin, as provided in the template
     * @var string
     */
    protected $name = "";

    /**
     * Styles that the xml of this string provides.
     *
     * @var ilSkinStyleXML[]
     */
    protected $styles = array();

    /**
     * Version of skin, as provided by the template
     *
     * @var string
     */
    protected $version = "0.1";


    /**
     * ilSkinXML constructor.
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->setId($id);
        $this->setName($name);
    }

    /**
     * @param string $path
     * @return ilSkinXML
     * @throws ilSystemStyleException
     */
    public static function parseFromXML($path = "")
    {
        try {
            $xml = new SimpleXMLElement(file_get_contents($path));
        } catch (Exception $e) {
            throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $path);
        }

        $id = basename(dirname($path));
        $skin = new self($id, (string) $xml->attributes()["name"]);
        $skin->setVersion((string) $xml->attributes()["version"]);

        /**
         * @var ilSkinStyleXML $last_style
         */
        $last_style = null;


        foreach ($xml->children() as $style_xml) {
            $style = ilSkinStyleXML::parseFromXMLElement($style_xml);

            /**
             * @var SimpleXMLElement $style_xml
             */
            if ($style_xml->getName() == "substyle") {
                if (!$last_style) {
                    throw new ilSystemStyleException(ilSystemStyleException::NO_PARENT_STYLE, $style->getId());
                }
                $style->setSubstyleOf($last_style->getId());
            } else {
                $last_style = $style;
            }
            $skin->addStyle($style);
        }
        return $skin;
    }

    /**
     * Stores the skin and all it's styles as xml.
     *
     * @return string
     */
    public function asXML()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><template/>');
        $xml->addAttribute("xmlns", "http://www.w3.org");
        $xml->addAttribute("version", $this->getVersion());
        $xml->addAttribute("name", $this->getName());

        $last_style = null;

        foreach ($this->getStyles() as $style) {
            if (!$style->isSubstyle()) {
                $this->addChildToXML($xml, $style);

                foreach ($this->getSubstylesOfStyle($style->getId()) as $substyle) {
                    $this->addChildToXML($xml, $substyle);
                }
            }
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }

    /**
     * Used to generate the xml for styles contained by the skin
     *
     * @param SimpleXMLElement $xml
     * @param ilSkinStyleXML $style
     */
    protected function addChildToXML(SimpleXMLElement $xml, ilSkinStyleXML $style)
    {
        $xml_style = null;
        if ($style->isSubstyle()) {
            $xml_style = $xml->addChild("substyle");
        } else {
            $xml_style = $xml->addChild("style");
        }
        $xml_style->addAttribute("id", $style->getId());
        $xml_style->addAttribute("name", $style->getName());
        $xml_style->addAttribute("image_directory", $style->getImageDirectory());
        $xml_style->addAttribute("css_file", $style->getCssFile());
        $xml_style->addAttribute("sound_directory", $style->getSoundDirectory());
        $xml_style->addAttribute("font_directory", $style->getFontDirectory());
    }

    /**
     * @param $path
     */
    public function writeToXMLFile($path)
    {
        file_put_contents($path, $this->asXML());
    }
    /**
     * @param ilSkinStyleXML $style
     */
    public function addStyle(ilSkinStyleXML $style)
    {
        $this->styles[] = $style;
    }

    /**
     * @param $id
     * @throws ilSystemStyleException
     */
    public function removeStyle($id)
    {
        foreach ($this->getStyles() as $index => $style) {
            if ($style->getId() == $id) {
                unset($this->styles[$index]);
                return;
            }
        }
        throw new ilSystemStyleException(ilSystemStyleException::INVALID_ID, $id);
    }

    /**
     * @param $id
     * @return ilSkinStyleXML
     * @throws ilSystemStyleException
     */
    public function getStyle($id)
    {
        foreach ($this->getStyles() as $style) {
            if ($style->getId() == $id) {
                return $style;
            }
        }
        throw new ilSystemStyleException(ilSystemStyleException::INVALID_ID, $id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasStyle($id)
    {
        foreach ($this->getStyles() as $style) {
            if ($style->getId() == $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ilSkinStyleXML
     */
    public function getDefaultStyle()
    {
        return array_values($this->styles)[0];
    }

    /**
     * Iterator implementations
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->styles) !== false;
    }

    /**
     * @return	mixed
     */
    public function key()
    {
        return key($this->styles);
    }

    /**
     * @return	mixed
     */
    public function current()
    {
        return current($this->styles);
    }

    public function next()
    {
        next($this->styles);
    }
    public function rewind()
    {
        reset($this->styles);
    }

    /**
     * Countable implementations
     */
    public function count()
    {
        return count($this->styles);
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
     * @return ilSkinStyleXML[]
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param ilSkinStyleXML[] $styles
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        if ($version != null && $version != '' && $this->isVersionChangeable()) {
            $this->version = $version;
        }
    }

    /**
     * @return string
     */
    public function getVersionStep($version)
    {
        if ($this->isVersionChangeable()) {
            $v = explode('.', ($version == "" ? '0.1' : $version));
            $v[count($v) - 1] = ($v[count($v) - 1] + 1);
            $this->version = implode('.', $v);
        }
        return $this->version;
    }

    public function isVersionChangeable()
    {
        return ($this->version != '$Id$');
    }

    /**
     * @param $style_id
     * @return array
     */
    public function getSubstylesOfStyle($style_id)
    {
        $substyles = array();

        if ($this->getStyle($style_id)) {
            foreach ($this->getStyles() as $style) {
                if ($style->getId() != $style_id && $style->isSubstyle()) {
                    if ($style->getSubstyleOf() == $style_id) {
                        $substyles[$style->getId()] = $style;
                    }
                }
            }
        }
        return $substyles;
    }

    /**
     * Returns wheter a given style has substyles
     * @param $style_id
     * @return bool
     */
    public function hasStyleSubstyles($style_id)
    {
        if ($this->getStyle($style_id)) {
            foreach ($this->getStyles() as $style) {
                if ($style->getId() != $style_id && $style->isSubstyle()) {
                    if ($style->getSubstyleOf() == $style_id) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasStyles()
    {
        return count($this->getStyles()) > 0;
    }
}
