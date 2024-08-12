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

use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;
use ILIAS\MetaData\XML\Copyright\CopyrightHandlerInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\XML\Reader\ReaderInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\Action;

class Legacy implements ReaderInterface
{
    protected MarkerFactoryInterface $marker_factory;
    protected ScaffoldProviderInterface $scaffold_provider;
    protected CopyrightHandlerInterface $copyright_handler;

    public function __construct(
        MarkerFactoryInterface $marker_factory,
        ScaffoldProviderInterface $scaffold_provider,
        CopyrightHandlerInterface $copyright_handler
    ) {
        $this->marker_factory = $marker_factory;
        $this->scaffold_provider = $scaffold_provider;
        $this->copyright_handler = $copyright_handler;
    }

    public function read(
        \SimpleXMLElement $xml,
        Version $version
    ): SetInterface {
        $set = $this->scaffold_provider->set();

        $this->prepareAddingOfGeneral($set, $xml->General);
        if (!empty($xml->Lifecycle)) {
            $this->prepareAddingOfLifeCycle($set, $xml->Lifecycle);
        }
        if (!empty($xml->{'Meta-Metadata'})) {
            $this->prepareAddingOfMetaMetadata($set, $xml->{'Meta-Metadata'});
        }
        if (!empty($xml->Technical)) {
            $this->prepareAddingOfTechnical($set, $xml->Technical);
        }
        if (!empty($xml->Educational)) {
            $this->prepareAddingOfEducational($set, $xml->Educational);
        }
        if (!empty($xml->Rights)) {
            $this->prepareAddingOfRights($set, $xml->Rights);
        }
        foreach ($xml->Relation as $relation_xml) {
            $this->prepareAddingOfRelation($set, $relation_xml);
        }
        foreach ($xml->Annotation as $annotation_xml) {
            $this->prepareAddingOfAnnotation($set, $annotation_xml);
        }
        foreach ($xml->Classification as $classification_xml) {
            $this->prepareAddingOfClassification($set, $classification_xml);
        }

        return $set;
    }

    protected function prepareAddingOfGeneral(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $general = $this->addScaffoldAndMark($set->getRoot(), 'general');

        foreach ($xml->Identifier as $identifier_xml) {
            $this->prepareAddingOfIdentifier($general, $identifier_xml);
        }

        $this->prepareAddingOfLangstring('title', $general, $xml->Title);

        foreach ($xml->Language as $language_xml) {
            $this->addScaffoldAndMark($general, 'language', (string) $language_xml->attributes()->Language);
        }

        foreach ($xml->Description as $description_xml) {
            $this->prepareAddingOfLangstring('description', $general, $description_xml);
        }

        foreach ($xml->Keyword as $keyword_xml) {
            $this->prepareAddingOfLangstring('keyword', $general, $keyword_xml);
        }

        if (!empty($xml->Coverage)) {
            $this->prepareAddingOfLangstring('coverage', $general, $xml->Coverage);
        }

        $this->prepareAddingOfVocabulary(
            'structure',
            (string) $xml->attributes()->Structure,
            $general
        );
    }

    protected function prepareAddingOfLifeCycle(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $lifecycle = $this->addScaffoldAndMark($set->getRoot(), 'lifeCycle');

        $this->prepareAddingOfLangstring('version', $lifecycle, $xml->Version);

        $this->prepareAddingOfVocabulary(
            'status',
            (string) $xml->attributes()->status,
            $lifecycle
        );

        foreach ($xml->Contribute as $contribute_xml) {
            $this->prepareAddingOfContribute($lifecycle, $contribute_xml);
        }
    }

    protected function prepareAddingOfMetaMetadata(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $metametadata = $this->addScaffoldAndMark($set->getRoot(), 'metaMetadata');

        foreach ($xml->Identifier as $identifier_xml) {
            $this->prepareAddingOfIdentifier($metametadata, $identifier_xml);
        }

        foreach ($xml->Contribute as $contribute_xml) {
            $this->prepareAddingOfContribute($metametadata, $contribute_xml);
        }

        $this->addScaffoldAndMark($metametadata, 'metadataSchema', 'LOMv1.0');

        if (!empty($xml->attributes()->Language)) {
            $this->addScaffoldAndMark($metametadata, 'language', (string) $xml->attributes()->Language);
        }
    }

    protected function prepareAddingOfTechnical(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $technical = $this->addScaffoldAndMark($set->getRoot(), 'technical');

        foreach ($xml->Format as $format_xml) {
            $this->addScaffoldAndMark($technical, 'format', (string) $format_xml);
        }

        if (!empty($xml->Size)) {
            $this->addScaffoldAndMark($technical, 'size', (string) $xml->Size);
        }

        foreach ($xml->Location as $location_xml) {
            $this->addScaffoldAndMark($technical, 'location', (string) $location_xml);
        }

        foreach ($xml->Requirement as $requirement_xml) {
            $this->prepareAddingOfRequirement($technical, $requirement_xml);
        }
        foreach ($xml->OrComposite as $or_composite_xml) {
            foreach ($or_composite_xml->Requirement as $requirement_xml) {
                $this->prepareAddingOfRequirement($technical, $requirement_xml);
            }
        }

        if (!empty($xml->InstallationRemarks)) {
            $this->prepareAddingOfLangstring(
                'installationRemarks',
                $technical,
                $xml->InstallationRemarks
            );
        }

        if (!empty($xml->OtherPlatformRequirements)) {
            $this->prepareAddingOfLangstring(
                'otherPlatformRequirements',
                $technical,
                $xml->OtherPlatformRequirements
            );
        }

        if (!empty($xml->Duration)) {
            $duration = $this->addScaffoldAndMark($technical, 'duration');
            $this->addScaffoldAndMark($duration, 'duration', (string) $xml->Duration);
        }
    }

    protected function prepareAddingOfEducational(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $educational = $this->addScaffoldAndMark($set->getRoot(), 'educational');

        $this->prepareAddingOfVocabulary(
            'interactivityType',
            (string) $xml->attributes()->InteractivityType,
            $educational
        );

        $this->prepareAddingOfVocabulary(
            'learningResourceType',
            (string) $xml->attributes()->LearningResourceType,
            $educational
        );

        $this->prepareAddingOfVocabulary(
            'interactivityLevel',
            (string) $xml->attributes()->InteractivityLevel,
            $educational
        );

        $this->prepareAddingOfVocabulary(
            'semanticDensity',
            (string) $xml->attributes()->SemanticDensity,
            $educational
        );

        $this->prepareAddingOfVocabulary(
            'intendedEndUserRole',
            (string) $xml->attributes()->IntendedEndUserRole,
            $educational
        );

        $this->prepareAddingOfVocabulary(
            'context',
            (string) $xml->attributes()->Context,
            $educational
        );

        foreach ($xml->TypicalAgeRange as $tar_xml) {
            $this->prepareAddingOfLangstring('typicalAgeRange', $educational, $tar_xml);
        }

        $this->prepareAddingOfVocabulary(
            'difficulty',
            (string) $xml->attributes()->Difficulty,
            $educational
        );

        if (!empty($xml->TypicalLearningTime)) {
            $duration = $this->addScaffoldAndMark($educational, 'typicalLearningTime');
            $this->addScaffoldAndMark($duration, 'duration', (string) $xml->TypicalLearningTime);
        }

        foreach ($xml->Description as $description_xml) {
            $this->prepareAddingOfLangstring('description', $educational, $description_xml);
        }

        foreach ($xml->Language as $language_xml) {
            $this->addScaffoldAndMark($educational, 'language', (string) $language_xml->attributes()->Language);
        }
    }

    protected function prepareAddingOfRights(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $rights = $this->addScaffoldAndMark($set->getRoot(), 'rights');

        $this->prepareAddingOfVocabulary(
            'cost',
            (string) $xml->attributes()->Cost,
            $rights
        );

        $this->prepareAddingOfVocabulary(
            'copyrightAndOtherRestrictions',
            (string) $xml->attributes()->CopyrightAndOtherRestrictions,
            $rights
        );

        $description_scaffold = $this->addScaffoldAndMark($rights, 'description');
        $this->addScaffoldAndMark(
            $description_scaffold,
            'language',
            (string) $xml->Description->attributes()->Language
        );
        $description_string = $this->copyright_handler->copyrightFromExport((string) $xml->Description);
        if ($description_string !== '') {
            $this->addScaffoldAndMark($description_scaffold, 'string', $description_string);
        }
    }

    protected function prepareAddingOfRelation(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $relation = $this->addScaffoldAndMark($set->getRoot(), 'relation');

        $this->prepareAddingOfVocabulary(
            'kind',
            (string) $xml->attributes()->Kind,
            $relation,
            true
        );

        $resource = $this->addScaffoldAndMark($relation, 'resource');
        $resource_xml = $xml->Resource;

        foreach ($resource_xml->Identifier_ as $identifier_xml) {
            $this->prepareAddingOfIdentifier($resource, $identifier_xml);
        }

        foreach ($resource_xml->Description as $description_xml) {
            $this->prepareAddingOfLangstring('description', $resource, $description_xml);
        }
    }

    protected function prepareAddingOfAnnotation(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $annotation = $this->addScaffoldAndMark($set->getRoot(), 'annotation');

        $this->addScaffoldAndMark($annotation, 'entity', (string) $xml->Entity);

        $date = $this->addScaffoldAndMark($annotation, 'date');
        $this->addScaffoldAndMark($date, 'dateTime', (string) $xml->Date);

        $this->prepareAddingOfLangstring('description', $annotation, $xml->Description);
    }

    protected function prepareAddingOfClassification(
        SetInterface $set,
        \SimpleXMLElement $xml
    ): void {
        $classification = $this->addScaffoldAndMark($set->getRoot(), 'classification');

        $this->prepareAddingOfVocabulary(
            'purpose',
            (string) $xml->attributes()->Purpose,
            $classification
        );

        foreach ($xml->TaxonPath as $taxon_path_xml) {
            $taxon_path = $this->addScaffoldAndMark($classification, 'taxonPath');

            $this->prepareAddingOfLangstring('source', $taxon_path, $taxon_path_xml->Source);

            foreach ($taxon_path_xml->taxon as $taxon_xml) {
                $taxon = $this->addScaffoldAndMark($taxon_path, 'taxon');

                if (!empty($taxon_xml->attributes()->Id)) {
                    $this->addScaffoldAndMark($taxon, 'id', (string) $taxon_xml->attributes()->Id);
                }

                $this->prepareAddingOfLangstring('entry', $taxon, $taxon_xml);
            }
        }

        $this->prepareAddingOfLangstring(
            'description',
            $classification,
            $xml->Description
        );

        foreach ($xml->Keyword as $keyword_xml) {
            $this->prepareAddingOfLangstring('keyword', $classification, $keyword_xml);
        }
    }

    protected function prepareAddingOfRequirement(
        ElementInterface $element,
        \SimpleXMLElement $xml
    ): void {
        $scaffold = $this->addScaffoldAndMark($element, 'requirement');

        foreach ($xml->Type->OperatingSystem as $os_xml) {
            $orc_scaffold = $this->addScaffoldAndMark($scaffold, 'orComposite');
            $this->prepareAddingOfVocabulary('type', 'operating system', $orc_scaffold);

            $name = (string) $os_xml->attributes()->Name;
            if ($name === 'MacOS') {
                $name = 'macos';
            }
            $this->prepareAddingOfVocabulary(
                'name',
                $name,
                $orc_scaffold
            );

            $min_version = (string) ($os_xml->attributes()->MinimumVersion ?? '');
            $max_version = (string) ($os_xml->attributes()->MaximumVersion ?? '');
            if ($min_version !== '') {
                $this->addScaffoldAndMark($orc_scaffold, 'minimumVersion', $min_version);
            }
            if ($max_version !== '') {
                $this->addScaffoldAndMark($orc_scaffold, 'maximumVersion', $max_version);
            }
        }

        foreach ($xml->Type->Browser as $browser_xml) {
            $orc_scaffold = $this->addScaffoldAndMark($scaffold, 'orComposite');
            $this->prepareAddingOfVocabulary('type', 'browser', $orc_scaffold);

            $name = (string) $browser_xml->attributes()->Name;
            if ($name !== 'Mozilla') {
                $this->prepareAddingOfVocabulary(
                    'name',
                    strtolower((string) $browser_xml->attributes()->Name),
                    $orc_scaffold
                );
            }

            $min_version = (string) ($browser_xml->attributes()->MinimumVersion ?? '');
            $max_version = (string) ($browser_xml->attributes()->MaximumVersion ?? '');
            if ($min_version !== '') {
                $this->addScaffoldAndMark($orc_scaffold, 'minimumVersion', $min_version);
            }
            if ($max_version !== '') {
                $this->addScaffoldAndMark($orc_scaffold, 'maximumVersion', $max_version);
            }
        }
    }

    protected function prepareAddingOfLangstring(
        string $name,
        ElementInterface $element,
        \SimpleXMLElement $xml
    ): void {
        $language = (string) $xml->attributes()->Language;
        $string = (string) $xml;

        $scaffold = $this->addScaffoldAndMark($element, $name);
        $this->addScaffoldAndMark($scaffold, 'language', $language);
        if ($string !== '') {
            $this->addScaffoldAndMark($scaffold, 'string', $string);
        }
    }

    protected function prepareAddingOfVocabulary(
        string $name,
        string $value,
        ElementInterface $element,
        bool $fill_spaces_in_value = false
    ): void {
        $value = $this->transformVocabValue($value, $fill_spaces_in_value);

        $scaffold = $this->addScaffoldAndMark($element, $name);
        $this->addScaffoldAndMark($scaffold, 'source', 'LOMv1.0');
        $this->addScaffoldAndMark($scaffold, 'value', $value);
    }

    protected function prepareAddingOfIdentifier(
        ElementInterface $element,
        \SimpleXMLElement $xml
    ): void {
        $catalog = (string) ($xml->attributes()->Catalog ?? '');
        $entry = (string) ($xml->attributes()->Entry ?? '');

        $scaffold = $this->addScaffoldAndMark($element, 'identifier');
        if ($catalog !== '') {
            $this->addScaffoldAndMark($scaffold, 'catalog', $catalog);
        }
        if ($entry !== '') {
            $this->addScaffoldAndMark($scaffold, 'entry', $entry);
        }
    }

    protected function prepareAddingOfContribute(
        ElementInterface $element,
        \SimpleXMLElement $xml
    ): void {
        $role = (string) ($xml->attributes()->Role ?? '');
        $date = (string) $xml->Date;

        $scaffold = $this->addScaffoldAndMark($element, 'contribute');
        $this->prepareAddingOfVocabulary('role', $role, $scaffold);
        foreach ($xml->Entity as $entity_xml) {
            $this->addScaffoldAndMark($scaffold, 'entity', (string) $entity_xml);
        }
        if ($date !== '') {
            $date_scaffold = $this->addScaffoldAndMark($scaffold, 'date');
            $this->addScaffoldAndMark($date_scaffold, 'dateTime', $date);
        }
    }

    protected function addScaffoldAndMark(
        ElementInterface $to_element,
        string $name,
        string $value = ''
    ): ElementInterface {
        $scaffold = $to_element->addScaffoldToSubElements($this->scaffold_provider, $name);
        $scaffold->mark($this->marker_factory, Action::CREATE_OR_UPDATE, $value);
        return $scaffold;
    }

    protected function transformVocabValue(string $value, bool $fill_spaces = false): string
    {
        $value = $this->camelCaseToSpaces($value);

        if ($fill_spaces) {
            $value = str_replace(' ', '', $value);
        }

        return $value;
    }

    protected function camelCaseToSpaces(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $string);
        return strtolower($string);
    }
}
