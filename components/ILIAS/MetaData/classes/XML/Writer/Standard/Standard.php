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

namespace ILIAS\MetaData\XML\Writer\Standard;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\XML\Dictionary\DictionaryInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\XML\Dictionary\TagInterface;
use ILIAS\MetaData\XML\Copyright\CopyrightHandlerInterface;
use ILIAS\MetaData\XML\Writer\WriterInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Manipulator\ManipulatorInterface;

class Standard implements WriterInterface
{
    protected DictionaryInterface $dictionary;
    protected CopyrightHandlerInterface $copyright_handler;
    protected PathFactory $path_factory;
    protected ManipulatorInterface $manipulator;

    public function __construct(
        DictionaryInterface $dictionary,
        CopyrightHandlerInterface $copyright_handler,
        PathFactory $path_factory,
        ManipulatorInterface $manipulator
    ) {
        $this->dictionary = $dictionary;
        $this->copyright_handler = $copyright_handler;
        $this->path_factory = $path_factory;
        $this->manipulator = $manipulator;
    }

    public function write(SetInterface $set): \SimpleXMLElement
    {
        $set = $this->prepareCopyright($set);
        $root = $set->getRoot();
        $root_name = $root->getDefinition()->name();
        $xml = new \SimpleXMLElement('<' . $root_name . '></' . $root_name . '>');

        $this->addSubElementsToXML(
            $root,
            $this->getTagForElement($root),
            $xml
        );

        return $xml;
    }

    protected function prepareCopyright(SetInterface $set): SetInterface
    {
        if (!$this->copyright_handler->isCopyrightSelectionActive()) {
            return $set;
        }

        $path_to_copyright = $this->path_factory->custom()
                                                ->withNextStep('rights')
                                                ->withNextStep('description')
                                                ->withNextStep('string')
                                                ->get();
        // markers are ignored here, so we can safely use the manipulator
        return $this->manipulator->prepareCreateOrUpdate($set, $path_to_copyright, '');
    }

    protected function addSubElementsToXML(
        ElementInterface $element,
        ?TagInterface $tag,
        \SimpleXMLElement $xml,
        int $depth = 0
    ): void {
        if ($depth > 30) {
            throw new \ilMDXMLException('LOM set is nested too deep.');
        }

        if ($tag?->isExportedAsLangString()) {
            $this->addLangStringToXML($element, $xml);
            return;
        }

        foreach ($element->getSubElements() as $sub_element) {
            $sub_tag = $this->getTagForElement($sub_element);
            $sub_name = $sub_element->getDefinition()->name();
            $sub_value = $this->getDataValue($sub_element->getData(), $sub_tag);

            if ($sub_tag?->isOmitted()) {
                continue;
            }

            if ($sub_tag?->isExportedAsAttribute()) {
                $xml->addAttribute($sub_name, (string) $sub_value);
                continue;
            }

            $child_xml = $xml->addChild($sub_name, $sub_value);
            $this->addSubElementsToXML($sub_element, $sub_tag, $child_xml, $depth + 1);
        }
    }

    protected function addLangStringToXML(
        ElementInterface $element,
        \SimpleXMLElement $xml
    ): void {
        $string_element = null;
        $language_element = null;
        foreach ($element->getSubElements() as $sub_element) {
            if ($sub_element->getDefinition()->name() === 'string') {
                $string_element = $sub_element;
            } elseif ($sub_element->getDefinition()->name() === 'language') {
                $language_element = $sub_element;
            }
        }

        $string_value = '';
        if (!is_null($string_element)) {
            $string_value = $this->getDataValue(
                $string_element->getData(),
                $this->getTagForElement($string_element)
            );
        }
        $string_xml = $xml->addChild(
            'string',
            $string_value
        );

        if (is_null($language_element)) {
            return;
        }
        $language_value = $this->getDataValue(
            $language_element->getData(),
            $this->getTagForElement($language_element)
        );
        $string_xml->addAttribute(
            'language',
            $language_value
        );
    }

    protected function getDataValue(
        DataInterface $data,
        ?TagInterface $tag
    ): ?string {
        if ($tag?->isTranslatedAsCopyright()) {
            return $this->copyright_handler->copyrightForExport($data->value());
        }

        switch ($data->type()) {
            case Type::NULL:
                return null;

            case Type::LANG:
                $value = $data->value();
                if ($value === 'xx') {
                    return 'none';
                }
                return $value;

            case Type::STRING:
            case Type::VOCAB_SOURCE:
            case Type::VOCAB_VALUE:
            case Type::DATETIME:
            case Type::NON_NEG_INT:
            case Type::DURATION:
            default:
                return $data->value();
        }
    }

    protected function getTagForElement(ElementInterface $element): ?TagInterface
    {
        return $this->dictionary->tagForElement($element, $this->currentVersion());
    }

    protected function currentVersion(): Version
    {
        return Version::V10_0;
    }
}
