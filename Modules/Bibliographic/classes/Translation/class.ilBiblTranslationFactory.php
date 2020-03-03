<?php

/**
 * Class ilBiblTranslationFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationFactory implements ilBiblTranslationFactoryInterface
{

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;
    /**
     * @var \ilBiblFieldFactoryInterface
     */
    protected $field_factory;


    /**
     * ilBiblTranslationFactory constructor.
     *
     * @param \ilBiblFieldFactoryInterface $field_factory
     */
    public function __construct(ilBiblFieldFactoryInterface $field_factory)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->field_factory = $field_factory;
    }


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return string
     */
    public function translate(ilBiblFieldInterface $field)
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


    /**
     * @inheritDoc
     */
    public function translateAttribute(ilBiblAttributeInterface $attribute)
    {
        $field = $this->field_factory->findOrCreateFieldOfAttribute($attribute);

        return $this->translate($field);
    }


    /**
     * @inheritDoc
     */
    public function translateAttributeString($type_id, ilBiblAttributeInterface $attribute)
    {
        $field = $this->getFieldFactory()
                      ->findOrCreateFieldByTypeAndIdentifier($type_id, $attribute->getIdentifier());

        return $this->translate($field);
    }


    /**
     * @return \ilBiblFieldFactoryInterface
     */
    public function getFieldFactory()
    {
        return $this->field_factory;
    }


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return string
     */
    private function translateInCore(ilBiblFieldInterface $field)
    {
        $prefix = $this->getFieldFactory()->getType()->getStringRepresentation();
        $middle = "default";
        $identifier = $field->getIdentifier();

        $topic = implode("_", [ $prefix, $middle, $identifier ]);

        return $this->dic->language()->txt(strtolower($topic));
    }


    /**
     * @inheritDoc
     */
    public function translationExistsForFieldAndUsersLanguage(ilBiblFieldInterface $field)
    {
        return !is_null($this->getInstanceForFieldAndUsersLanguage($field));
    }


    /**
     * @inheritDoc
     */
    public function translationExistsForFieldAndSystemsLanguage(ilBiblFieldInterface $field)
    {
        return !is_null($this->getInstanceForFieldAndSystemsLanguage($field));
    }


    /**
     * @inheritDoc
     */
    public function translationExistsForField(ilBiblFieldInterface $field)
    {
        return $this->getCollectionOfTranslationsForField($field)->hasSets();
    }


    /**
     * @inheritDoc
     */
    public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field)
    {
        global $DIC;

        return $this->getCollectionOfTranslationsForField($field)
                    ->where([ "language_key" => $DIC->user()->getCurrentLanguage(), ])
                    ->first();
    }


    /**
     * @inheritDoc
     */
    public function getInstanceForFieldAndSystemsLanguage(ilBiblFieldInterface $field)
    {
        global $DIC;
        $lng = $DIC->language()->getDefaultLanguage();

        return $this->getCollectionOfTranslationsForField($field)
                    ->where([ "language_key" => $lng, ])
                    ->first();
    }


    /**
     * @inheritDoc
     */
    public function findArCreateInstanceForFieldAndlanguage(ilBiblFieldInterface $field, $language_key)
    {
        $inst = $this->getCollectionOfTranslationsForField($field)
                     ->where([ "language_key" => $language_key, ])
                     ->get();

        if (!$inst) {
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
    public function getAllTranslationsForField(ilBiblFieldInterface $field)
    {
        return $this->getCollectionOfTranslationsForField($field)->get();
    }


    /**
     * @inheritDoc
     */
    public function getAllTranslationsForFieldAsArray(ilBiblFieldInterface $field)
    {
        return $this->getCollectionOfTranslationsForField($field)->getArray();
    }


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return \ActiveRecordList
     */
    private function getCollectionOfTranslationsForField(ilBiblFieldInterface $field)
    {
        return ilBiblTranslation::where([ 'field_id' => $field->getId() ])->orderBy('language_key');
    }


    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        return ilBiblTranslation::findOrFail($id);
    }


    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        self::findById($id)->delete();

        return true;
    }
}
