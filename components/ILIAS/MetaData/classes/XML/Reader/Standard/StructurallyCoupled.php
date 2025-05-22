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

namespace ILIAS\MetaData\XML\Reader\Standard;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;
use ILIAS\MetaData\XML\Dictionary\DictionaryInterface;
use ILIAS\MetaData\XML\Dictionary\TagInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\XML\Copyright\CopyrightHandlerInterface;
use ILIAS\MetaData\XML\Reader\ReaderInterface;

class StructurallyCoupled implements ReaderInterface
{
    protected MarkerFactoryInterface $marker_factory;
    protected ScaffoldProviderInterface $scaffold_provider;
    protected DictionaryInterface $dictionary;
    protected CopyrightHandlerInterface $copyright_handler;

    public function __construct(
        MarkerFactoryInterface $marker_factory,
        ScaffoldProviderInterface $scaffold_provider,
        DictionaryInterface $dictionary,
        CopyrightHandlerInterface $copyright_handler
    ) {
        $this->marker_factory = $marker_factory;
        $this->scaffold_provider = $scaffold_provider;
        $this->dictionary = $dictionary;
        $this->copyright_handler = $copyright_handler;
    }

    /**
     * Assumes that the structure of the xml is identical to the structure of
     * LOM in ILIAS, with exceptions defined in the dictionary.
     */
    public function read(
        \SimpleXMLElement $xml,
        Version $version
    ): SetInterface {
        $set = $this->scaffold_provider->set();
        $root_element = $set->getRoot();

        if ($xml->getName() !== $root_element->getDefinition()->name()) {
            throw new \ilMDXMLException(
                $xml->getName() . ' is not the correct root element, should be ' .
                $root_element->getDefinition()->name()
            );
        }

        $this->prepareAddingSubElementsFromXML(
            $version,
            $root_element,
            $this->dictionary->tagForElement($root_element, $version),
            $xml
        );

        return $set;
    }

    protected function prepareAddingSubElementsFromXML(
        Version $version,
        ElementInterface $element,
        ?TagInterface $tag,
        \SimpleXMLElement $xml,
        int $depth = 0
    ): void {
        if ($depth > 30) {
            throw new \ilMDXMLException('LOM XML is nested too deep.');
        }

        if ($tag?->isExportedAsLangString()) {
            $this->prepareAddingLangStringFromXML($version, $element, $xml);
            return;
        }

        $children_and_attributes = new \AppendIterator();
        if (!empty($children = $xml->children())) {
            $children_and_attributes->append($children);
        }
        if (!empty($attributes = $xml->attributes())) {
            $children_and_attributes->append($attributes);
        }
        /** @var \SimpleXMLElement $child_or_attrib_xml */
        foreach ($children_and_attributes as $child_or_attrib_xml) {
            $sub_scaffold = $element->addScaffoldToSubElements(
                $this->scaffold_provider,
                $child_or_attrib_xml->getName(),
            );
            if (is_null($sub_scaffold)) {
                continue;
            }

            $sub_tag = $this->dictionary->tagForElement($sub_scaffold, $version);
            if ($sub_tag?->isOmitted()) {
                continue;
            }

            $sub_value = $this->parseElementValue(
                $sub_scaffold->getDefinition(),
                $sub_tag,
                (string) $child_or_attrib_xml
            );
            $sub_scaffold->mark($this->marker_factory, Action::CREATE_OR_UPDATE, $sub_value);

            $this->prepareAddingSubElementsFromXML(
                $version,
                $sub_scaffold,
                $sub_tag,
                $child_or_attrib_xml,
                $depth + 1
            );
        }
    }

    protected function prepareAddingLangStringFromXML(
        Version $version,
        ElementInterface $element,
        \SimpleXMLElement $xml,
    ): void {
        $string_xml = $xml->string;
        $language_xml = $string_xml->attributes()->language;

        if (!empty($string_xml) && ((string) $string_xml) !== '') {
            $string_element = $element->addScaffoldToSubElements(
                $this->scaffold_provider,
                'string'
            );
            $string_element->mark(
                $this->marker_factory,
                Action::CREATE_OR_UPDATE,
                $this->parseElementValue(
                    $string_element->getDefinition(),
                    $this->dictionary->tagForElement($string_element, $version),
                    (string) $string_xml
                )
            );
        }

        if (!empty($language_xml)) {
            $language_element = $element->addScaffoldToSubElements(
                $this->scaffold_provider,
                'language'
            );
            $language_element->mark(
                $this->marker_factory,
                Action::CREATE_OR_UPDATE,
                $this->parseElementValue(
                    $language_element->getDefinition(),
                    $this->dictionary->tagForElement($language_element, $version),
                    (string) $language_xml
                )
            );
        }
    }

    protected function parseElementValue(
        DefinitionInterface $definition,
        ?TagInterface $tag,
        string $value
    ): string {
        $value = strip_tags($value);

        if ($tag?->isTranslatedAsCopyright()) {
            return $this->copyright_handler->copyrightFromExport($value);
        }

        switch ($definition->dataType()) {
            case Type::NULL:
                return '';

            case Type::LANG:
                if ($value === 'none') {
                    return 'xx';
                }
                return $value;

            case Type::STRING:
            case Type::VOCAB_SOURCE:
            case Type::VOCAB_VALUE:
            case Type::DATETIME:
            case Type::NON_NEG_INT:
            case Type::DURATION:
            default:
                return $value;
        }
    }
}
