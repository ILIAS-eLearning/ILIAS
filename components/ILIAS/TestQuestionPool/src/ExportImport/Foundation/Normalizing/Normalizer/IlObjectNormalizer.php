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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer;

use ILIAS\ILIASObject\Properties\CoreProperties\Online;
use ILIAS\ILIASObject\Properties\CoreProperties\TitleAndDescription;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\TitleAndIconVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\HeaderActionVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\InfoTabVisibility;
use ILIAS\ILIASObject\Properties\Translations\Language;
use ILIAS\ILIASObject\Properties\Translations\Translations;
use ILIAS\ILIASObject\Properties\Properties;
use ILIAS\ILIASObject\Properties\Property;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ilObject;
use ilObjectFactory;

/**
 * @implements Normalizer<ilObject, array>
 */
#[Normalizes(ilObject::class)]
class IlObjectNormalizer implements Normalizer
{
    public function __construct(
        protected readonly Transformations $tt,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilObject) {
            throw new NormalizingException('Invalid value', $value);
        }

        // Icon, tile, translations and container settings are exported by the ILIASObject component.
        // See: ilObjectDataSet
        return [
            'obj_id' => $this->tt->normalize(new Id($value->getId(), 'object')),
            'title' => $value->getTitle(),
            'description' => $value->getLongDescription(),
            'type' => $value->getType(),
            'owner' => $value->getOwner(),
            'create_date' => $value->getCreateDate(),
            'last_update' => $value->getLastUpdateDate(),
            'import_id' => $value->getImportId(),
            'properties' => $this->normalizeProperties($value->getObjectProperties()),
        ];
    }

    private function normalizeProperties(Properties $properties): array
    {
        return [
            'owner' => $properties->getOwner(),
            'import_id' => $properties->getImportId(),
            'title_and_description' => $this->normalizeProperty($properties->getPropertyTitleAndDescription()),
            'title_and_icon_visibility' => $this->normalizeProperty($properties->getPropertyTitleAndIconVisibility()),
            'header_action_visibility' => $this->normalizeProperty($properties->getPropertyHeaderActionVisibility()),
            'info_tab_visibility' => $this->normalizeProperty($properties->getPropertyInfoTabVisibility()),
            'translations' => $this->normalizeTranslations($properties->getPropertyTranslations()),
        ];
    }

    private function normalizeProperty(Property $property): array|bool|int|string|null
    {
        if ($property instanceof TitleAndDescription) {
            return [
                'title' => $property->getTitle(),
                'description' => $property->getDescription(),
                'long_description' => $property->getLongDescription(),
            ];
        }

        if ($property instanceof Online) {
            return $property->getIsOnline();
        }

        if (
            $property instanceof TitleAndIconVisibility
            || $property instanceof HeaderActionVisibility
            || $property instanceof InfoTabVisibility
        ) {
            return $property->getVisibility();
        }

        return null;
    }

    private function normalizeTranslations(Translations $translations): array
    {
        return [
            'default_language' => $translations->getDefaultLanguage(),
            'base_language' => $translations->getBaseLanguage(),
            'languages' => array_map(fn(Language $language): array => [
                'language_code' => $language->getLanguageCode(),
                'title' => $language->getTitle(),
                'description' => $language->getDescription(),
            ], $translations->getLanguages()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilObject
    {
        if ($type !== ilObject::class && !in_array(ilObject::class, class_parents($type))) {
            throw new NormalizingException("Invalid type for ilObject: {$type}");
        }

        // Validate the class of the object by its type field
        $object_type = $this->tt->string($value['type']);
        $object_class = ilObjectFactory::getClassByType($object_type);
        if ($object_class !== $type) {
            throw new NormalizingException("Expected {$type}, got object of type {$object_type} ({$object_class})");
        }

        // Create new object instance without id to avoid reading the object from the database
        $object = new $object_class(0, false);

        $object->setId($this->tt->denormalize($value['obj_id'], Id::class)->getId());
        $object->setTitle($this->tt->string($value['title']));
        $object->setDescription($this->tt->string($value['description']));
        $object->setType($this->tt->string($value['type']));
        $object->setOwner($this->tt->int($value['owner']));
        $object->setImportId($this->tt->string($value['import_id']));

        return $object;
    }
}
