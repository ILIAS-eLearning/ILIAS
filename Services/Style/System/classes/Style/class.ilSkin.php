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
 * ilSkin holds an manages the basic data of a skin as provide by the template of the skin. This class is also
 * responsible to read this data from the xml and, after manipulations transfer the data back to xml.
 *
 * To read a skin from xml do not use this class, us ilSkinContainer instead.
 */
class ilSkin implements Iterator, Countable
{
    /**
     * ID of the skin, equals the name of the folder this skin is stored in
     */
    protected string $id = '';


    /**
     * Name of the skin, as provided in the template
     */
    protected string $name = '';

    /**
     * Styles that the xml of this string provides.
     *
     * @var ilSkinStyle[]
     */
    protected array $styles = [];

    /**
     * Version of skin, as provided by the template
     */
    protected string $version = '0.1';

    public function __construct(string $id, string $name)
    {
        $this->setId($id);
        $this->setName($name);
    }

    /**
     * Stores the skin and all it's styles as xml.
     */
    public function asXML(): string
    {
        $xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><template/>");
        $xml->addAttribute('xmlns', 'http://www.w3.org');
        $xml->addAttribute('version', $this->getVersion());
        $xml->addAttribute('name', $this->getName());

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
     */
    protected function addChildToXML(SimpleXMLElement $xml, ilSkinStyle $style): void
    {
        if ($style->isSubstyle()) {
            $xml_style = $xml->addChild('substyle');
        } else {
            $xml_style = $xml->addChild('style');
        }
        $xml_style->addAttribute('id', $style->getId());
        $xml_style->addAttribute('name', $style->getName());
        $xml_style->addAttribute('image_directory', $style->getImageDirectory());
        $xml_style->addAttribute('css_file', $style->getCssFile());
        $xml_style->addAttribute('sound_directory', $style->getSoundDirectory());
        $xml_style->addAttribute('font_directory', $style->getFontDirectory());
    }

    public function writeToXMLFile(string $path): void
    {
        file_put_contents($path, $this->asXML());
    }

    public function addStyle(ilSkinStyle $style): void
    {
        $this->styles[] = $style;
    }

    /**
     * @throws ilSystemStyleException
     */
    public function removeStyle(string $id): void
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
     * @throws ilSystemStyleException
     */
    public function getStyle(string $id): ilSkinStyle
    {
        foreach ($this->getStyles() as $style) {
            if ($style->getId() == $id) {
                return $style;
            }
        }
        throw new ilSystemStyleException(ilSystemStyleException::INVALID_ID, $id);
    }

    public function hasStyle(string $id): bool
    {
        foreach ($this->getStyles() as $style) {
            if ($style->getId() == $id) {
                return true;
            }
        }
        return false;
    }

    public function getDefaultStyle(): ilSkinStyle
    {
        return array_values($this->styles)[0];
    }

    public function valid(): bool
    {
        return current($this->styles) !== false;
    }

    public function key(): int
    {
        return key($this->styles);
    }

    public function current(): ilSkinStyle
    {
        return current($this->styles);
    }

    public function next(): void
    {
        next($this->styles);
    }

    public function rewind(): void
    {
        reset($this->styles);
    }

    public function count(): int
    {
        return count($this->styles);
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

    /**
     * @return ilSkinStyle[]
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * @param ilSkinStyle[] $styles
     */
    public function setStyles(array $styles): void
    {
        $this->styles = $styles;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        if ($version != '' && $this->isVersionChangeable()) {
            $this->version = $version;
        }
    }

    public function getVersionStep(string $version): string
    {
        if ($this->isVersionChangeable()) {
            $v = explode('.', ($version == '' ? '0.1' : $version));
            $count = count($v) ;
            $v[$count - 1] = ((int) $v[$count - 1] + 1); //ToDo PHP8 Review: You are adding an int to a string in strict_types.
            $this->version = implode('.', $v);
        }
        return $this->version;
    }

    public function isVersionChangeable(): bool
    {
        return ($this->version != '$Id$');
    }

    public function updateParentStyleOfSubstyles(string $old_parent_style_id, string $new_parent_style_id): void
    {
        if ($this->hasStyleSubstyles($old_parent_style_id)) {
            foreach ($this->getSubstylesOfStyle($old_parent_style_id) as $substyle) {
                $substyle->setSubstyleOf($new_parent_style_id);
            }
        }
    }

    /**
     * @return ilSkinStyle[]
     */
    public function getSubstylesOfStyle(string $style_id): array
    {
        $substyles = [];

        foreach ($this->getStyles() as $style) {
            if ($style->getId() != $style_id && $style->isSubstyle()) {
                if ($style->getSubstyleOf() == $style_id) {
                    $substyles[$style->getId()] = $style;
                }
            }
        }

        return $substyles;
    }

    /**
     * Returns wheter a given style has substyles
     */
    public function hasStyleSubstyles(string $style_id): bool
    {
        foreach ($this->getStyles() as $style) {
            if ($style->getId() != $style_id && $style->isSubstyle()) {
                if ($style->getSubstyleOf() == $style_id) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hasStyles(): bool
    {
        return count($this->getStyles()) > 0;
    }
}
