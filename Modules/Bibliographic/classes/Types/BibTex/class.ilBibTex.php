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
