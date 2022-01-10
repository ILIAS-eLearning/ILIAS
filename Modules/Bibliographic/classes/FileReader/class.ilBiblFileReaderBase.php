<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilBiblFileReaderBase
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBiblFileReaderBase implements ilBiblFileReaderInterface
{

    /**
     * Number of maximum allowed characters for attributes in order to fit in the database
     * @var int
     */
    const ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH = 4000;
    const ENCODING_UTF_8 = 'UTF-8';
    const ENCODING_ASCII = 'ASCII';
    const ENCODING_ISO_8859_1 = 'ISO-8859-1';
    protected string $file_content = '';
    protected string $path_to_file = '';
    protected \ilBiblEntryFactoryInterface $entry_factory;
    protected \ilBiblFieldFactoryInterface $field_factory;
    protected \ilBiblAttributeFactoryInterface $attribute_factory;
    /**
     * @var \ILIAS\ResourceStorage\Services
     */
    protected $storage;

    /**
     * ilBiblFileReaderBase constructor.
     * @param ilBiblEntryFactoryInterface $entry_factory
     */
    public function __construct(
        ilBiblEntryFactoryInterface $entry_factory,
        ilBiblFieldFactoryInterface $field_factory,
        ilBiblAttributeFactoryInterface $attribute_factory
    ) {
        global $DIC;

        $this->entry_factory = $entry_factory;
        $this->field_factory = $field_factory;
        $this->attribute_factory = $attribute_factory;
        $this->storage = $DIC["resource_storage"];
    }

    /**
     * @param ResourceIdentification $identification
     * @return bool
     */
    public function readContent(ResourceIdentification $identification) : bool
    {
        $stream = $this->storage->consume()->stream($identification)->getStream();
        $this->setFileContent($stream->getContents());

        return true;
    }

    /**
     * @param $string
     */
    protected function convertStringToUTF8($string): string
    {
        if (!function_exists('mb_detect_encoding') || !function_exists('mb_detect_order')
            || !function_exists("mb_convert_encoding")
        ) {
            return $string;
        }
        try {
            ob_end_clean();
        } catch (Throwable $t) {
            //
        }

        $mb_detect_encoding = mb_detect_encoding($string);
        mb_detect_order(array(self::ENCODING_UTF_8, self::ENCODING_ISO_8859_1));
        switch ($mb_detect_encoding) {
            case self::ENCODING_UTF_8:
                break;
            case self::ENCODING_ASCII:
                $string = utf8_encode(iconv(self::ENCODING_ASCII, 'UTF-8//IGNORE', $string));
                break;
            default:
                $string = mb_convert_encoding($string, self::ENCODING_UTF_8, $mb_detect_encoding);
                break;
        }

        return $string;
    }

    public function getFileContent(): string
    {
        return $this->file_content;
    }

    public function setFileContent(string $file_content)
    {
        $this->file_content = $file_content;
    }

    /**
     * @inheritDoc
     */
    public function parseContentToEntries(ilObjBibliographic $bib): array
    {
        $entries_from_file = $this->parseContent();
        $entry_instances = [];
        //fill each entry into a ilBibliographicEntry object and then write it to DB by executing doCreate()
        foreach ($entries_from_file as $file_entry) {
            $type = null;
            $x = 0;
            $parsed_entry = array();
            foreach ($file_entry as $key => $attribute) {
                // if the attribute is an array, make a comma separated string out of it
                if (is_array($attribute)) {
                    $attribute = implode(", ", $attribute);
                }
                // reduce the attribute strings to a maximum of 4000 (ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) characters, in order to fit in the database
                //if (mb_strlen($attribute, 'UTF-8') > self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) {
                if (ilStr::strLen($attribute) > self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) {
                    // $attribute = mb_substr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH - 3, 'UTF-8') . '...';
                    $attribute = ilStr::subStr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH - 3) . '...';
                }
                // ty (RIS) or entryType (BIB) is the type and is treated seperately
                if (strtolower($key) == 'ty' || strtolower($key) == 'entrytype') {
                    $type = $attribute;
                    continue;
                }
                //TODO - Refactoring for ILIAS 4.5 - get rid off array restructuring
                //change array structure (name not as the key, but under the key "name")
                $parsed_entry[$x]['name'] = $key;
                $parsed_entry[$x]['value'] = $attribute;
                $x++;
            }
            /**
             * @var $entry_model ilBiblEntry
             */
            //create the entry and fill data into database by executing doCreate()
            $entry_factory = $this->getEntryFactory();
            $entry_model = $entry_factory->getEmptyInstance();
            $entry_model->setType($type);
            $entry_model->setDataId($bib->getId());
            $entry_model->store();
            foreach ($parsed_entry as $entry) {
                $this->getAttributeFactory()->createAttribute($entry['name'], $entry['value'], $entry_model->getId());
            }
            //$entry_model->doCreate();
            $entry_instances[] = $entry_model;
        }

        return $entry_instances;
    }

    /**
     * @inheritdoc
     */
    public function getEntryFactory() : ilBiblEntryFactoryInterface
    {
        return $this->entry_factory;
    }

    /**
     * @inheritdoc
     */
    public function getFieldFactory() : ilBiblFieldFactoryInterface
    {
        return $this->field_factory;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeFactory() : ilBiblAttributeFactoryInterface
    {
        return $this->attribute_factory;
    }
}
