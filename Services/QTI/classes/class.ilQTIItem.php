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
    public ?string $ident;
    public ?string $title;
    public ?string $maxattempts;
    public ?string $label;
    public ?string $xmllang;
    public ?string $comment;
    public ?string $ilias_version;
    public ?string $author;
    public ?string $questiontype;
    /** @var null|array{h: string, m: string, s: string} */
    public ?array $duration;
    public ?ilQTIMaterial $questiontext;
    /** @var ilQTIResprocessing[] */
    public array $resprocessing;
    /** @var ilQTIItemfeedback[] */
    public array $itemfeedback;
    public ?ilQTIPresentation $presentation;
    /** @var (ilQTIResponse|ilQTIMaterial|null)[] */
    public array $presentationitem;
    /**
     * @var array{solution: ilQTIMattext, gap_index: int}[]
     */
    public array $suggested_solutions;
    /**
     * @var array{label: string, entry: string}[]
     */
    public array $itemmetadata;
    protected ?string $iliasSourceVersion;
    protected ?string $iliasSourceNic;
    protected array $response;

    public function __construct()
    {
        $this->ident = null;
        $this->title = null;
        $this->maxattempts = null;
        $this->label = null;
        $this->xmllang = null;
        $this->comment = null;
        $this->ilias_version = null;
        $this->author = null;
        $this->questiontype = null;
        $this->duration = null;
        $this->questiontext = null;
        $this->response = [];
        $this->resprocessing = [];
        $this->itemfeedback = [];
        $this->presentation = null;
        $this->presentationitem = [];
        $this->suggested_solutions = [];
        $this->itemmetadata = [];
        $this->iliasSourceVersion = null;
        $this->iliasSourceNic = null;
    }

    public function setIdent(string $a_ident) : void
    {
        $this->ident = $a_ident;
    }

    public function getIdent() : ?string
    {
        return $this->ident;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setComment(string $a_comment) : void
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

    public function getComment() : ?string
    {
        return $this->comment;
    }

    public function setDuration(string $a_duration) : void
    {
        if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $a_duration, $matches)) {
            $this->duration = array(
                "h" => $matches[4],
                "m" => $matches[5],
                "s" => $matches[6]
            );
        }
    }

    /**
     * @return null|array{h: string, m: string, s: string}
     */
    public function getDuration() : ?array
    {
        return $this->duration;
    }
    
    public function setQuestiontext(ilQTIMaterial $a_questiontext) : void
    {
        $this->questiontext = $a_questiontext;
    }
    
    public function getQuestiontext() : ?ilQTIMaterial
    {
        return $this->questiontext;
    }
    
    public function addResprocessing(?ilQTIResprocessing $a_resprocessing) : void
    {
        $this->resprocessing[] = $a_resprocessing;
    }
    
    public function addItemfeedback(?ilQTIItemfeedback $a_itemfeedback) : void
    {
        $this->itemfeedback[] = $a_itemfeedback;
    }
    
    public function setMaxattempts(string $a_maxattempts) : void
    {
        $this->maxattempts = $a_maxattempts;
    }
    
    public function getMaxattempts() : ?string
    {
        return $this->maxattempts;
    }

    public function setLabel(string $a_label) : void
    {
        $this->label = $a_label;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setXmllang(string $a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang() : ?string
    {
        return $this->xmllang;
    }

    public function setPresentation(ilQTIPresentation $a_presentation) : void
    {
        $this->presentation = $a_presentation;
    }

    public function getPresentation() : ?ilQTIPresentation
    {
        return $this->presentation;
    }
    
    public function collectResponses() : void
    {
    }

    public function setQuestiontype(string $a_questiontype) : void
    {
        $this->questiontype = $a_questiontype;
    }

    public function getQuestiontype() : ?string
    {
        return $this->questiontype;
    }

    /**
     * @param ilQTIResponse|ilQTIMaterial|null $a_presentationitem
     */
    public function addPresentationitem($a_presentationitem) : void
    {
        $this->presentationitem[] = $a_presentationitem;
    }

    public function determineQuestionType() : ?string
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

    public function setAuthor(string $a_author) : void
    {
        $this->author = $a_author;
    }

    public function getAuthor() : ?string
    {
        return $this->author;
    }

    public function getIliasSourceVersion() : ?string
    {
        return $this->iliasSourceVersion;
    }

    public function setIliasSourceVersion(string $iliasSourceVersion) : void
    {
        $this->iliasSourceVersion = $iliasSourceVersion;
    }

    public function getIliasSourceNic() : ?string
    {
        return $this->iliasSourceNic;
    }

    public function setIliasSourceNic(?string $iliasSourceNic) : void
    {
        $this->iliasSourceNic = $iliasSourceNic;
    }
    
    public function addSuggestedSolution(ilQTIMattext $a_solution, int $a_gap_index) : void
    {
        $this->suggested_solutions[] = array("solution" => $a_solution, "gap_index" => $a_gap_index);
    }

    /**
     * @param array{label: string, entry: string} $a_metadata
     */
    public function addMetadata(array $a_metadata) : void
    {
        $this->itemmetadata[] = $a_metadata;
    }
    
    public function getMetadata() : array
    {
        return $this->itemmetadata;
    }

    public function getMetadataEntry(string $a_label) : ?string
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
