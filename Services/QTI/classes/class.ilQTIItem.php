<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

const QT_UNKNOWN = "unknown";
const QT_KPRIM_CHOICE = "assKprimChoice";
const QT_LONG_MENU = "assLongMenu";
const QT_MULTIPLE_CHOICE_SR = "assSingleChoice";
const QT_MULTIPLE_CHOICE_MR = "assMultipleChoice";
const QT_CLOZE = "assClozeTest";
const QT_ERRORTEXT = "assErrorText";
const QT_MATCHING = "assMatchingQuestion";
const QT_ORDERING = "assOrderingQuestion";
const QT_ORDERING_HORIZONTAL = "assOrderingHorizontal";
const QT_IMAGEMAP = "assImagemapQuestion";
const QT_TEXT = "assTextQuestion";
const QT_FILEUPLOAD = "assFileUpload";
const QT_NUMERIC = "assNumeric";
const QT_FORMULA = "assFormulaQuestion";
const QT_TEXTSUBSET = "assTextSubset";

/**
 * QTI item class
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @version $Id$
 *
 * @package assessment
 */
class ilQTIItem
{
    public $ident;
    public $title;
    public $maxattempts;
    public $label;
    public $xmllang;
    
    public $comment;

    /** @var string|null */
    public $ilias_version;

    /** @var string|null */
    public $author;

    /** @var string|null */
    public $questiontype;
    public $duration;
    public $questiontext;

    /** @var array */
    public $resprocessing;

    /** @var array */
    public $itemfeedback;

    /** @var ilQTIPresentation|null */
    public $presentation;

    /** @var array */
    public $presentationitem;

    /**
     * @var array [['solution' => string, 'gap_index' => string]] // @todo Check if really strings.
     */
    public $suggested_solutions;

    /**
     * @var array [['label' => string]]
     */
    public $itemmetadata;

    /** @var string|null */
    protected $iliasSourceVersion;
    protected $iliasSourceNic;

    protected array $response;

    public function __construct()
    {
        $this->response = array();
        $this->resprocessing = array();
        $this->itemfeedback = array();
        $this->presentation = null;
        $this->presentationitem = array();
        $this->suggested_solutions = array();
        $this->itemmetadata = array();
        
        $this->iliasSourceVersion = null;
        $this->iliasSourceNic = null;
    }
    
    public function setIdent($a_ident) : void
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }
    
    public function setTitle($a_title) : void
    {
        $this->title = $a_title;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setComment($a_comment) : void
    {
        if (preg_match("/(.*?)\=(.*)/", $a_comment, $matches)) {
            // special comments written by ILIAS
            switch ($matches[1]) {
                case "ILIAS Version":
                    $this->ilias_version = $matches[2];
                    return;
                case "Questiontype":
                    $this->questiontype = $matches[2];
                    return;
                case "Author":
                    $this->author = $matches[2];
                    return;
            }
        }
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setDuration($a_duration) : void
    {
        if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $a_duration, $matches)) {
            $this->duration = array(
                "h" => $matches[4],
                "m" => $matches[5],
                "s" => $matches[6]
            );
        }
    }
    
    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setQuestiontext($a_questiontext) : void
    {
        $this->questiontext = $a_questiontext;
    }
    
    public function getQuestiontext()
    {
        return $this->questiontext;
    }
    
    public function addResprocessing($a_resprocessing) : void
    {
        $this->resprocessing[] = $a_resprocessing;
    }
    
    public function addItemfeedback($a_itemfeedback) : void
    {
        $this->itemfeedback[] = $a_itemfeedback;
    }
    
    public function setMaxattempts($a_maxattempts) : void
    {
        $this->maxattempts = $a_maxattempts;
    }
    
    public function getMaxattempts()
    {
        return $this->maxattempts;
    }
    
    public function setLabel($a_label) : void
    {
        $this->label = $a_label;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setXmllang($a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }
    
    public function getXmllang()
    {
        return $this->xmllang;
    }

    /**
     * @param ilQTIPresentation
     */
    public function setPresentation($a_presentation) : void
    {
        $this->presentation = $a_presentation;
    }
    
    public function getPresentation()
    {
        return $this->presentation;
    }
    
    public function collectResponses() : void
    {
    }

    /**
     * @param string $a_questiontype
     */
    public function setQuestiontype($a_questiontype) : void
    {
        $this->questiontype = $a_questiontype;
    }

    /**
     * @return string|null
     */
    public function getQuestiontype()
    {
        return $this->questiontype;
    }
    
    public function addPresentationitem($a_presentationitem) : void
    {
        $this->presentationitem[] = $a_presentationitem;
    }

    /**
     * @return string|null
     */
    public function determineQuestionType()
    {
        switch ($this->questiontype) {
            case "ORDERING QUESTION":
                return QT_ORDERING;
            case "KPRIM CHOICE QUESTION":
                return QT_KPRIM_CHOICE;
            case "LONG MENU QUESTION":
                return QT_LONG_MENU;
            case "SINGLE CHOICE QUESTION":
                return QT_MULTIPLE_CHOICE_SR;
            case "MULTIPLE CHOICE QUESTION":
                break;
            case "MATCHING QUESTION":
                return QT_MATCHING;
            case "CLOZE QUESTION":
                return QT_CLOZE;
            case "IMAGE MAP QUESTION":
                return QT_IMAGEMAP;
            case "TEXT QUESTION":
                return QT_TEXT;
            case "NUMERIC QUESTION":
                return QT_NUMERIC;
            case "TEXTSUBSET QUESTION":
                return QT_TEXTSUBSET;
        }
        if (!$this->presentation) {
            return QT_UNKNOWN;
        }
        foreach ($this->presentation->order as $entry) {
            if ('response' === $entry["type"]) {
                $result = $this->typeFromResponse($this->presentation->response[$entry["index"]]);
                if (null !== $result) {
                    return $result;
                }
            }
        }
        if (strlen($this->questiontype) == 0) {
            return QT_UNKNOWN;
        }

        return $this->questiontype;
    }

    /**
     * @param string $a_author
     */
    public function setAuthor($a_author) : void
    {
        $this->author = $a_author;
    }

    /**
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getIliasSourceVersion()
    {
        return $this->iliasSourceVersion;
    }

    /**
     * @param string $iliasSourceVersion
     */
    public function setIliasSourceVersion($iliasSourceVersion) : void
    {
        $this->iliasSourceVersion = $iliasSourceVersion;
    }

    /**
     * @return null
     */
    public function getIliasSourceNic()
    {
        return $this->iliasSourceNic;
    }

    /**
     * @param null $iliasSourceNic
     */
    public function setIliasSourceNic($iliasSourceNic) : void
    {
        $this->iliasSourceNic = $iliasSourceNic;
    }
    
    public function addSuggestedSolution($a_solution, $a_gap_index) : void
    {
        $this->suggested_solutions[] = array("solution" => $a_solution, "gap_index" => $a_gap_index);
    }
    
    public function addMetadata($a_metadata) : void
    {
        $this->itemmetadata[] = $a_metadata;
    }
    
    public function getMetadata()
    {
        return $this->itemmetadata;
    }

    /**
     * @return null|string
     */
    public function getMetadataEntry($a_label)
    {
        foreach ($this->itemmetadata as $metadata) {
            if ($metadata["label"] === $a_label) {
                return $metadata["entry"];
            }
        }
        return null;
    }

    private function typeFromResponse(ilQTIResponse $response) : ?string
    {
        switch ($response->getResponsetype()) {
            case RT_RESPONSE_LID:
                switch ($response->getRCardinality()) {
                    case R_CARDINALITY_ORDERED: return QT_ORDERING;
                    case R_CARDINALITY_SINGLE: return QT_MULTIPLE_CHOICE_SR;
                    case R_CARDINALITY_MULTIPLE: return QT_MULTIPLE_CHOICE_MR;
                }
                // no break
            case RT_RESPONSE_XY: return QT_IMAGEMAP;
            case RT_RESPONSE_STR:
                switch ($response->getRCardinality()) {
                    case R_CARDINALITY_ORDERED: return QT_TEXT;
                    case R_CARDINALITY_SINGLE: return QT_CLOZE;
                }
                // no break
            case RT_RESPONSE_GRP: return QT_MATCHING;

            default: return null;
        }
    }
}
