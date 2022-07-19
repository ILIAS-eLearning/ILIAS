<?php declare(strict_types=1);

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
 ********************************************************************
 */

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
    public const QT_UNKNOWN = "unknown";
    public const QT_KPRIM_CHOICE = "assKprimChoice";
    public const QT_LONG_MENU = "assLongMenu";
    public const QT_MULTIPLE_CHOICE_SR = "assSingleChoice";
    public const QT_MULTIPLE_CHOICE_MR = "assMultipleChoice";
    public const QT_CLOZE = "assClozeTest";
    public const QT_ERRORTEXT = "assErrorText";
    public const QT_MATCHING = "assMatchingQuestion";
    public const QT_ORDERING = "assOrderingQuestion";
    public const QT_ORDERING_HORIZONTAL = "assOrderingHorizontal";
    public const QT_IMAGEMAP = "assImagemapQuestion";
    public const QT_TEXT = "assTextQuestion";
    public const QT_FILEUPLOAD = "assFileUpload";
    public const QT_NUMERIC = "assNumeric";
    public const QT_FORMULA = "assFormulaQuestion";
    public const QT_TEXTSUBSET = "assTextSubset";

    public ?string $ident = null;
    public string $title = '';
    public string $maxattempts = '';
    public ?string $label = null;
    public ?string $xmllang = null;
    public string $comment = '';
    public ?string $ilias_version = null;
    public string $author = '';
    public ?string $questiontype = null;
    /** @var null|array{h: string, m: string, s: string} */
    public ?array $duration = null;
    public ?ilQTIMaterial $questiontext = null;
    /** @var ilQTIResprocessing[] */
    public array $resprocessing = [];
    /** @var ilQTIItemfeedback[] */
    public array $itemfeedback = [];
    public ?ilQTIPresentation $presentation = null;
    /** @var (ilQTIResponse|ilQTIMaterial|null)[] */
    public array $presentationitem = [];
    /**
     * @var array{solution: ilQTIMattext, gap_index: int}[]
     */
    public array $suggested_solutions = [];
    /**
     * @var array{label: string, entry: string}[]
     */
    public array $itemmetadata = [];
    protected ?string $iliasSourceVersion = null;
    protected ?string $iliasSourceNic = null;
    protected array $response = [];

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

    public function getTitle() : string
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
                    $this->author = $matches[2] ?? '';
                    return;
            }
        }
        $this->comment = $a_comment;
    }

    public function getComment() : string
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
    
    public function addResprocessing(ilQTIResprocessing $a_resprocessing) : void
    {
        $this->resprocessing[] = $a_resprocessing;
    }
    
    public function addItemfeedback(ilQTIItemfeedback $a_itemfeedback) : void
    {
        $this->itemfeedback[] = $a_itemfeedback;
    }
    
    public function setMaxattempts(string $a_maxattempts) : void
    {
        $this->maxattempts = $a_maxattempts;
    }
    
    public function getMaxattempts() : string
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
                return self::QT_ORDERING;
            case "KPRIM CHOICE QUESTION":
                return self::QT_KPRIM_CHOICE;
            case "LONG MENU QUESTION":
                return self::QT_LONG_MENU;
            case "SINGLE CHOICE QUESTION":
                return self::QT_MULTIPLE_CHOICE_SR;
            case "MULTIPLE CHOICE QUESTION":
                break;
            case "MATCHING QUESTION":
                return self::QT_MATCHING;
            case "CLOZE QUESTION":
                return self::QT_CLOZE;
            case "IMAGE MAP QUESTION":
                return self::QT_IMAGEMAP;
            case "TEXT QUESTION":
                return self::QT_TEXT;
            case "NUMERIC QUESTION":
                return self::QT_NUMERIC;
            case "TEXTSUBSET QUESTION":
                return self::QT_TEXTSUBSET;
        }
        if (!$this->presentation) {
            return self::QT_UNKNOWN;
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
            return self::QT_UNKNOWN;
        }

        return $this->questiontype;
    }

    public function setAuthor(string $a_author) : void
    {
        $this->author = $a_author;
    }

    public function getAuthor() : string
    {
        return $this->author;
    }

    public function getIliasSourceVersion() : ?string
    {
        return $this->iliasSourceVersion;
    }

    public function setIliasSourceVersion(?string $iliasSourceVersion) : void
    {
        $this->iliasSourceVersion = $iliasSourceVersion ?? '';
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
            case ilQTIResponse::RT_RESPONSE_LID:
                switch ($response->getRCardinality()) {
                    case ilQTIResponse::R_CARDINALITY_ORDERED: return self::QT_ORDERING;
                    case ilQTIResponse::R_CARDINALITY_SINGLE: return self::QT_MULTIPLE_CHOICE_SR;
                    case ilQTIResponse::R_CARDINALITY_MULTIPLE: return self::QT_MULTIPLE_CHOICE_MR;
                }
                // no break
            case ilQTIResponse::RT_RESPONSE_XY: return self::QT_IMAGEMAP;
            case ilQTIResponse::RT_RESPONSE_STR:
                switch ($response->getRCardinality()) {
                    case ilQTIResponse::R_CARDINALITY_ORDERED: return self::QT_TEXT;
                    case ilQTIResponse::R_CARDINALITY_SINGLE: return self::QT_CLOZE;
                }
                // no break
            case ilQTIResponse::RT_RESPONSE_GRP: return self::QT_MATCHING;

            default: return null;
        }
    }
}
