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
 
/**
 * Class ilBiblTranslationFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationFactory implements ilBiblTranslationFactoryInterface
{
    protected \ILIAS\DI\Container $dic;
    protected \ilBiblFieldFactoryInterface $field_factory;

    /**
     * ilBiblTranslationFactory constructor.
     */
    public function __construct(ilBiblFieldFactoryInterface $field_factory)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->field_factory = $field_factory;
    }

    public function translate(ilBiblFieldInterface $field) : string
    {
        if ($this->translationExistsForFieldAndUsersLanguage($field)) {
            return $this->getInstanceForFieldAndUsersLanguage($field)->getTranslation();
        }
        if ($this->translationExistsForFieldAndSystemsLanguage($field)) {
            return $this->getInstanceForFieldAndSystemsLanguage($field)->getTranslation();
        }

        $core_translation = $this->translateInCore($field);
        if (strpos($core_translation, "-") !== 0) {
            return $core_translation;
        }

        return $field->getIdentifier();
    }

    public function translateAttribute(ilBiblAttributeInterface $attribute) : string
    {
        $field = $this->field_factory->findOrCreateFieldOfAttribute($attribute);

        return $this->translate($field);
    }


    public function translateAttributeString(int $type_id, ilBiblAttributeInterface $attribute) : string
    {
        $field = $this->getFieldFactory()
                      ->findOrCreateFieldByTypeAndIdentifier($type_id, $attribute->getIdentifier());

        return $this->translate($field);
    }

    public function getFieldFactory() : \ilBiblFieldFactoryInterface
    {
        return $this->field_factory;
    }

    private function translateInCore(ilBiblFieldInterface $field) : string
    {
        $prefix = $this->getFieldFactory()->getType()->getStringRepresentation();
        $middle = "default";
        $identifier = $field->getIdentifier();

        $topic = implode("_", [$prefix, $middle, $identifier]);

        return $this->dic->language()->txt(strtolower($topic));
    }

    /**
     * @inheritDoc
     */
    public function translationExistsForFieldAndUsersLanguage(ilBiblFieldInterface $field) : bool
    {
        return !is_null($this->getInstanceForFieldAndUsersLanguage($field));
    }

    /**
     * @inheritDoc
     */
    public function translationExistsForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : bool
    {
        return !is_null($this->getInstanceForFieldAndSystemsLanguage($field));
    }

    /**
     * @inheritDoc
     */
    public function translationExistsForField(ilBiblFieldInterface $field) : bool
    {
        return $this->getCollectionOfTranslationsForField($field)->hasSets();
    }

    /**
     * @inheritDoc
     */
    public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface
    {
        global $DIC;
    
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getCollectionOfTranslationsForField($field)
                    ->where(["language_key" => $DIC->user()->getCurrentLanguage(),])
                    ->first();
    }

    /**
     * @inheritDoc
     */
    public function getInstanceForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface
    {
        global $DIC;
        $lng = $DIC->language()->getDefaultLanguage();
    
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getCollectionOfTranslationsForField($field)
                    ->where(["language_key" => $lng,])
                    ->first();
    }

    /**
     * @inheritDoc
     */
    public function findArCreateInstanceForFieldAndlanguage(
        ilBiblFieldInterface $field,
        string $language_key
    ) : \ilBiblTranslationInterface {
        $inst = $this->getCollectionOfTranslationsForField($field)
                     ->where(["language_key" => $language_key,])
                     ->get();

        if ($inst === []) {
            $inst = new ilBiblTranslation();
            $inst->setFieldId($field->getId());
            $inst->setLanguageKey($language_key);
            $inst->create();
        }

        return $inst;
    }

    /**
     * @inheritDoc
     */
    public function getAllTranslationsForField(ilBiblFieldInterface $field) : array
    {
        return $this->getCollectionOfTranslationsForField($field)->get();
    }

    /**
     * @inheritDoc
     */
    public function getAllTranslationsForFieldAsArray(ilBiblFieldInterface $field) : array
    {
        return $this->getCollectionOfTranslationsForField($field)->getArray();
    }

    private function getCollectionOfTranslationsForField(ilBiblFieldInterface $field) : \ActiveRecordList
    {
        return ilBiblTranslation::where(['field_id' => $field->getId()])->orderBy('language_key');
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id) : \ilBiblTranslationInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblTranslation::findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $id) : bool
    {
        self::findById($id)->delete();

        return true;
    }
}
