<?php

/**
 * Class ilRisInterface
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilRis implements ilBiblTypeInterface
{

    /**
     * @inheritDoc
     */
    public function getId() : int
    {
        return ilBiblTypeFactoryInterface::DATA_TYPE_RIS;
    }


    /**
     * @inheritDoc
     */
    public function getStringRepresentation() : string
    {
        return "ris";
    }


    /**
     * @inheritDoc
     */
    public function getStandardFieldIdentifiers() : array
    {
        return self::$standard_fields;
    }


    /**
     * @var array
     *
     * @source https://en.wikipedia.org/wiki/RIS_(file_format)
     */
    protected static $standard_fields
        = array(
            'A1', // First Author
            'A2', // Secondary Author (each author on its own line preceded by the tag)
            'A3', // Tertiary Author (each author on its own line preceded by the tag)
            'A4', // Subsidiary Author (each author on its own line preceded by the tag)
            'AB', // Abstract
            'AD', // Author Address
            'AN', // Accession Number
            'AU', // Author (each author on its own line preceded by the tag)
            'AV', // Location in Archives
            'BT', // This field can contain alphanumeric characters; There is no practical limit to the length of this field.
            'C1', // Custom 1
            'C2', // Custom 2
            'C3', // Custom 3
            'C4', // Custom 4
            'C5', // Custom 5
            'C6', // Custom 6
            'C7', // Custom 7
            'C8', // Custom 8
            'CA', // Caption
            'CN', // Call Number
            'CP', // This field can contain alphanumeric characters; There is no practical limit to the length of this field.
            'CT', // Title of unpublished reference
            'CY', // Place Published
            'DA', // Date
            'DB', // Name of Database
            'DO', // DOI
            'DP', // Database Provider
            'ED', // Editor
            'EP', // End Page
            'ET', // Edition
            'ID', // Reference ID
            'IS', // Issue number
            'J1', // Periodical name: user abbreviation 1. This is an alphanumeric field of up to 255 characters.
            'J2', // Alternate Title (this field is used for the abbreviated title of a book or journal name, the latter mapped to T2)
            'JA', // Periodical name: standard abbreviation. This is the periodical in which the article was (or is to be, in the case of in-press references) published. This is an alphanumeric field of up to 255 characters.
            'JF', // Journal/Periodical name: full format. This is an alphanumeric field of up to 255 characters.
            'JO', // Journal/Periodical name: full format. This is an alphanumeric field of up to 255 characters.
            'KW', // Keywords (keywords should be entered each on its own line preceded by the tag)
            'L1', // Link to PDF. There is no practical limit to the length of this field. URL addresses can be entered individually, one per tag or multiple addresses can be entered on one line using a semi-colon as a separator.
            'L2', // Link to Full-text. There is no practical limit to the length of this field. URL addresses can be entered individually, one per tag or multiple addresses can be entered on one line using a semi-colon as a separator.
            'L3', // Related Records. There is no practical limit to the length of this field.
            'L4', // Image(s). There is no practical limit to the length of this field.
            'LA', // Language
            'LB', // Label
            'LK', // Website Link
            'M1', // Number
            'M2', // Miscellaneous 2. This is an alphanumeric field and there is no practical limit to the length of this field.
            'M3', // Type of Work
            'N1', // Notes
            'N2', // Abstract. This is a free text field and can contain alphanumeric characters; there is no practical length limit to this field.
            'NV', // Number of Volumes
            'OP', // Original Publication
            'PB', // Publisher
            'PP', // Publishing Place
            'PY', // Publication year (YYYY/MM/DD)
            'RI', // Reviewed Item
            'RN', // Research Notes
            'RP', // Reprint Edition
            'SE', // Section
            'SN', // ISBN/ISSN
            'SP', // Start Page
            'ST', // Short Title
            'T1', // Primary Title
            'T2', // Secondary Title (journal title, if applicable)
            'T3', // Tertiary Title
            'TA', // Translated Author
            'TI', // Title
            'TT', // Translated Title
            'U1', // User definable 1. This is an alphanumeric field and there is no practical limit to the length of this field.
            'U2', // User definable 2. This is an alphanumeric field and there is no practical limit to the length of this field.
            'U3', // User definable 3. This is an alphanumeric field and there is no practical limit to the length of this field.
            'U4', // User definable 4. This is an alphanumeric field and there is no practical limit to the length of this field.
            'U5', // User definable 5. This is an alphanumeric field and there is no practical limit to the length of this field.
            'UR', // URL
            'VL', // Volume number
            'VO', // Published Standard number
            'Y1', // Primary Date
            'Y2',
            // Access Date
        );
    /**
     * @var array
     */
    protected static $entry_types
        = array(
            'ABST', 'ADVS', 'ART', 'BILL', 'BOOK', 'CASE', 'CHAP', 'COMP', 'CONF', 'CTLG', 'DATA', 'ELEC', 'GEN', 'HEAR', 'ICOMM', 'INPR', 'JFULL', 'JOUR', 'MAP', 'MGZN', 'MPCT', 'MUSIC', 'NEWS', 'PAMP', 'PAT', 'PCOMM', 'RPRT', 'SER', 'SLIDE', 'SOUND', 'STAT', 'THES', 'UNBILl', 'UNPB', 'VIDEO',
        );


    /**
     * @param $field_name
     *
     * @return bool
     */
    public function isStandardField(string $field_name) : bool
    {
        return in_array(strtoupper($field_name), self::$standard_fields);
    }


    /**
     * @param $entry_ype
     *
     * @return bool
     */
    public function isEntryType(string $entry_ype) : bool
    {
        return in_array(strtoupper($entry_ype), self::$entry_types);
    }
}
