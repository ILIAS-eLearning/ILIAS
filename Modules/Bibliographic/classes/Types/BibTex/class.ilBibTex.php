<?php

/**
 * Class ilBibTexInterface
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibTex implements ilBiblTypeInterface
{

    /**
     * @inheritDoc
     */
    public function getId() : int
    {
        return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
    }


    /**
     * @inheritDoc
     */
    public function getStringRepresentation() : string
    {
        return "bib";
    }


    /**
     * @inheritDoc
     */
    public function getStandardFieldIdentifiers() : array
    {
        return self::$standard_fields;
    }



    protected static array $standard_fields
        = array(
            'address',
            'annote',
            'author',
            'booktitle',
            'chapter',
            'crossref',
            'edition',
            'editor',
            'eprint',
            'howpublished',
            'institution',
            'journal',
            'key',
            'month',
            'note',
            'number',
            'organization',
            'pages',
            'publisher',
            'school',
            'series',
            'title',
            'type',
            'url',
            'volume',
            'year',
        );
    protected static array $entry_types
        = array(
            'article',
            'book',
            'booklet',
            'conference',
            'inbook',
            'incollection',
            'inproceedings',
            'manual',
            'mastersthesis',
            'misc',
            'phdthesis',
            'proceedings',
            'techreport',
            'unpublished',
        );


    /**
     * @inheritdoc
     */
    public function isStandardField(string $field_name) : bool
    {
        return in_array($field_name, self::$standard_fields);
    }


    /**
     * @inheritdoc
     */
    public function isEntryType(string $entry_ype) : bool
    {
        return in_array($entry_ype, self::$entry_types);
    }
}
