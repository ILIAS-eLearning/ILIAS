<?php

declare(strict_types=1);

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
 * QTI Parser
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @version $Id$
 *
 * @extends ilSaxParser
 * @package assessment
 */
class ilQTIParser extends ilSaxParser
{
    public const IL_MO_PARSE_QTI = 1;
    public const IL_MO_VERIFY_QTI = 2;

    public bool $hasRootElement = false;

    /**
     * @var array<int, string>
     */
    public array $path = [];

    /**
     * @var ilQTIItem[]
     */
    public array $items = [];

    public ?ilQTIItem $item = null;

    /**
     * @var SplObjectStorage<XmlParser|resource, int>
     */
    public $depth;

    public string $qti_element = "";

    public bool $in_presentation = false;

    public bool $in_response = false;

    /**
     * @var ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public $render_type = null;

    public ?ilQTIResponseLabel $response_label = null;

    public ?ilQTIMaterial $material = null;

    public ?ilQTIMatimage $matimage = null;

    public ?ilQTIResponse $response = null;

    public ?ilQTIResprocessing $resprocessing = null;

    public ?ilQTIOutcomes $outcomes = null;

    public ?ilQTIDecvar $decvar = null;

    public ?ilQTIRespcondition $respcondition = null;

    public ?ilQTISetvar $setvar = null;

    public ?ilQTIDisplayfeedback $displayfeedback = null;

    public ?ilQTIItemfeedback $itemfeedback = null;

    /**
     * @var ilQTIFlowMat[]
     */
    public array $flow_mat = [];

    public int $flow = 0;

    public ?ilQTIPresentation $presentation = null;

    public ?ilQTIMattext $mattext = null;

    public bool $sametag = false;

    public string $characterbuffer = "";

    public ?ilQTIConditionvar $conditionvar = null;

    public int $parser_mode = 0;

    protected $solutionhint = null;
    public $solutionhints = [];

    /**
     * @var string[]
     */
    public array $import_idents = [];

    public int $qpl_id = 0;

    public ?int $tst_id = null;

    public ?ilObjTest $tst_object = null;

    public bool $do_nothing = false;

    public int $gap_index = 0;

    /**
     * @var ilQTIAssessment[]
     */
    public array $assessments = [];

    public ?ilQTIAssessment $assessment = null;

    public ?ilQTIAssessmentcontrol $assessmentcontrol = null;

    public ?ilQTIObjectives $objectives = null;

    public bool $in_assessment = false;

    public ?ilQTISection $section = null;

    /**
     * @var array<string, {test: mixed}>
     */
    public array $import_mapping = [];

    public int $question_counter = 1;

    public bool $in_itemmetadata = false;

    public bool $in_objectives = false;

    /**
     * @var array{title: string, type: string, ident: string}[]
     */
    public array $founditems = [];

    public bool $verifyroot = false;

    public int $verifyqticomment = 0;

    public int $verifymetadatafield = 0;

    public int $verifyfieldlabel = 0;

    public string $verifyfieldlabeltext = "";

    public int $verifyfieldentry = 0;

    public string $verifyfieldentrytext = "";

    protected int $numImportedItems = 0;

    protected ?ilQTIPresentationMaterial $prensentation_material = null;

    protected bool $in_prensentation_material = false;

    protected bool $ignoreItemsEnabled = false;

    private ?ilQTIMatapplet $matapplet = null;

    /**
     * @var array{label: string, entry: string}
     */
    private array $metadata = ["label" => "", "entry" => ""];

    private ?ilQTIResponseVar $responsevar = null;

    protected ?string $questionSetType = null;

    public function __construct(?string $a_xml_file, int $a_mode = self::IL_MO_PARSE_QTI, int $a_qpl_id = 0, $a_import_idents = "")
    {
        global $lng;

        $this->parser_mode = $a_mode;

        parent::__construct($a_xml_file);

        $this->qpl_id = $a_qpl_id;
        $this->lng = &$lng;
        $this->depth = new SplObjectStorage();
    }

    public function isIgnoreItemsEnabled(): bool
    {
        return $this->ignoreItemsEnabled;
    }

    public function setIgnoreItemsEnabled(bool $ignoreItemsEnabled): void
    {
        $this->ignoreItemsEnabled = $ignoreItemsEnabled;
    }

    public function getQuestionSetType(): ?string
    {
        return $this->questionSetType;
    }

    public function setQuestionSetType(string $questionSetType): void
    {
        $this->questionSetType = $questionSetType;
    }

    public function setTestObject(ilObjTest $a_tst_object): void
    {
        $this->tst_object = $a_tst_object;
        $this->tst_id = $this->tst_object->getId();
    }

    public function getTestObject(): ilObjTest
    {
        return $this->tst_object;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    *
    * @param XMLParser|resource $a_xml_parser
    */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function startParsing(): void
    {
        $this->question_counter = 1;
        parent::startParsing();
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function getParent($a_xml_parser): string
    {
        if ($this->depth[$a_xml_parser] > 0) {
            return $this->path[$this->depth[$a_xml_parser] - 1];
        }

        return "";
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param array<string, string> $a_attribs
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($this->parser_mode) {
            case self::IL_MO_PARSE_QTI:
                $this->handlerParseBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case self::IL_MO_VERIFY_QTI:
                $this->handlerVerifyBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param array<string, string> $a_attribs
     */
    public function handlerParseBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        if ($this->do_nothing) {
            return;
        }
        $this->sametag = false;
        $this->characterbuffer = "";
        $this->depth[$a_xml_parser] = ($this->depth[$a_xml_parser] ?? 0) + 1; // Issue with SplObjectStorage: Cannot use ++.
        $this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
        $this->qti_element = $a_name;

        switch (strtolower($a_name)) {
            case "assessment":
                $this->assessmentBeginTag($a_attribs);
                break;
            case "assessmentcontrol":
                $this->assessmentControlBeginTag($a_attribs);
                break;
            case "objectives":
                $this->objectives = new ilQTIObjectives();
                $this->in_objectives = true;
                break;
            case 'presentation_material':
                $this->prensentation_material = new ilQTIPresentationMaterial();
                $this->in_prensentation_material = true;
                break;
            case "section":
                $this->section = new ilQTISection();
                break;
            case "itemmetadata":
                $this->in_itemmetadata = true;
                break;
            case "qtimetadatafield":
                $this->metadata = ["label" => "", "entry" => ""];
                break;
            case "flow":
                $this->flow++;
                break;
            case "flow_mat":
                $this->flow_mat[] = new ilQTIFlowMat();
                break;
            case "itemfeedback":
                $this->itemFeedbackBeginTag($a_attribs);
                break;
            case "displayfeedback":
                $this->displayFeedbackBeginTag($a_attribs);
                break;
            case "setvar":
                $this->setVarBeginTag($a_attribs);
                break;
            case "conditionvar":
                $this->conditionvar = new ilQTIConditionvar();
                break;
            case "not":
                if ($this->conditionvar != null) {
                    $this->conditionvar->addNot();
                }
                break;
            case "and":
                if ($this->conditionvar != null) {
                    $this->conditionvar->addAnd();
                }
                break;
            case "or":
                if ($this->conditionvar != null) {
                    $this->conditionvar->addOr();
                }
                break;
            case "varequal":
                $this->varEqualBeginTag($a_attribs);
                break;
            case "varlt":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_LT);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "varlte":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_LTE);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "vargt":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_GT);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "vargte":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_GTE);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "varsubset":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_SUBSET);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "setmatch":
                            $this->responsevar->setSetmatch($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "varinside":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_INSIDE);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "areatype":
                            $this->responsevar->setAreatype($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "varsubstring":
                $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_SUBSTRING);
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "case":
                            $this->responsevar->setCase($value);
                            break;
                        case "respident":
                            $this->responsevar->setRespident($value);
                            break;
                        case "index":
                            $this->responsevar->setIndex($value);
                            break;
                    }
                }
                break;
            case "respcondition":
                $this->respcondition = new ilQTIRespcondition();
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "continue":
                            $this->respcondition->setContinue($value);
                            break;
                        case "title":
                            $this->respcondition->setTitle($value);
                            break;
                    }
                }
                break;
            case "outcomes":
                $this->outcomes = new ilQTIOutcomes();
                break;
            case "decvar":
                $this->decVarBeginTag($a_attribs);
                break;
            case "matimage":
                $this->matImageBeginTag($a_attribs);
                break;
            case "material":
                $this->materialBeginTag($a_attribs);
                break;
            case "mattext":
                $this->matTextBeginTag($a_attribs);
                break;
            case "matapplet":
                $this->matAppletBeginTag($a_attribs);
                break;
            case "questestinterop":
                $this->hasRootElement = true;
                break;
            case "qticomment":
                break;
            case "objectbank":
                // not implemented yet
                break;
            case "presentation":
                $this->in_presentation = true;
                $this->presentation = new ilQTIPresentation();
                break;
            case "response_label":
                $this->responseLabelBeginTag($a_attribs);
                break;
            case "render_choice":
                $this->renderChoiceBeginTag($a_attribs);
                break;
            case "render_hotspot":
                $this->renderHotspotBeginTag($a_attribs);
                break;
            case "render_fib":
                $this->renderFibBeginTag($a_attribs);
                break;
            case "response_lid":
                // Ordering Terms and Definitions    or
                // Ordering Terms and Pictures       or
                // Multiple choice single response   or
                // Multiple choice multiple response
            case "response_xy":
                // Imagemap question
            case "response_str":
                // Close question
            case "response_num":
            case "response_grp":
                // Matching terms and definitions
                // Matching terms and images
                $this->termsAndDefinitionsBeginTag($a_name, $a_attribs);
                break;
            case "item":
                $this->itemBeginTag($a_attribs);
                break;
            case "resprocessing":
                $this->resprocessingBeginTag($a_attribs);
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        switch ($this->parser_mode) {
            case self::IL_MO_PARSE_QTI:
                $this->handlerParseEndTag($a_xml_parser, $a_name);
                break;
            case self::IL_MO_VERIFY_QTI:
                $this->handlerVerifyEndTag($a_xml_parser, $a_name);
                break;
        }
    }

    /**
     * @noinspection NotOptimalIfConditionsInspection
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerParseEndTag($a_xml_parser, string $a_name): void
    {
        if ($this->do_nothing && strtolower($a_name) !== "item") {
            return;
        }
        switch (strtolower($a_name)) {
            case "assessment":
                if (is_object($this->tst_object)) {
                    $this->tst_object->fromXML($this->assessment);
                }
                $this->in_assessment = false;
                break;
            case "assessmentcontrol":
                $this->assessment->addAssessmentcontrol($this->assessmentcontrol);
                $this->assessmentcontrol = null;
                break;
            case "objectives":
                if (strtolower($this->getParent($a_xml_parser)) === "assessment") {
                    $this->assessment->addObjectives($this->objectives);
                }
                $this->in_objectives = false;
                break;
            case 'presentation_material':
                $this->assessment->setPresentationMaterial($this->prensentation_material);
                $this->in_prensentation_material = false;
                break;
            case "itemmetadata":
                $this->in_itemmetadata = false;
                break;
            case "qtimetadatafield":
                // handle only specific ILIAS metadata
                switch ($this->metadata["label"]) {
                    case "ILIAS_VERSION":
                        if ($this->item != null) {
                            $this->item->setIliasSourceVersion(
                                $this->fetchNumericVersionFromVersionDateString($this->metadata["entry"])
                            );
                        }
                        break;
                    case "QUESTIONTYPE":
                        if ($this->item != null) {
                            $this->item->setQuestiontype($this->metadata["entry"]);
                        }
                        break;
                    case "AUTHOR":
                        if ($this->item != null) {
                            $this->item->setAuthor($this->metadata["entry"]);
                        }
                        // no break
                    default:
                        if ($this->item != null) {
                            $this->item->addMetadata($this->metadata);
                        }
                        break;
                }
                if ($this->in_assessment) {
                    $this->assessment->addQtiMetadata($this->metadata);
                }
                $this->metadata = ["label" => "", "entry" => ""];
                break;
            case "flow":
                $this->flow--;
                break;
            case "flow_mat":
                if (count($this->flow_mat)) {
                    $flow_mat = array_pop($this->flow_mat);
                    if (count($this->flow_mat)) {
                        $this->flow_mat[count($this->flow_mat) - 1]->addFlowMat($flow_mat);
                    } elseif ($this->in_prensentation_material) {
                        $this->prensentation_material->addFlowMat($flow_mat);
                    } elseif ($this->itemfeedback != null) {
                        $this->itemfeedback->addFlowMat($flow_mat);
                    } elseif ($this->response_label != null) {
                        $this->response_label->addFlow_mat($flow_mat);
                    }
                }
                break;
            case "itemfeedback":
                if ($this->item != null) {
                    if ($this->itemfeedback != null) {
                        $this->item->addItemfeedback($this->itemfeedback);
                    }
                }
                $this->itemfeedback = null;
                break;
            case "displayfeedback":
                if ($this->respcondition != null) {
                    if ($this->displayfeedback != null) {
                        $this->respcondition->addDisplayfeedback($this->displayfeedback);
                    }
                }
                $this->displayfeedback = null;
                break;
            case "setvar":
                if ($this->respcondition != null) {
                    if ($this->setvar != null) {
                        $this->respcondition->addSetvar($this->setvar);
                    }
                }
                $this->setvar = null;
                break;
            case "conditionvar":
                if ($this->respcondition != null) {
                    $this->respcondition->setConditionvar($this->conditionvar);
                }
                $this->conditionvar = null;
                break;
            case "varequal":
            case "varlt":
            case "varlte":
            case "vargt":
            case "vargte":
            case "varsubset":
            case "varinside":
            case "varsubstring":
                if ($this->conditionvar != null) {
                    if ($this->responsevar != null) {
                        $this->conditionvar->addResponseVar($this->responsevar);
                    }
                }
                $this->responsevar = null;
                break;
            case "respcondition":
                if ($this->resprocessing != null) {
                    $this->resprocessing->addRespcondition($this->respcondition);
                }
                $this->respcondition = null;
                break;
            case "outcomes":
                if ($this->resprocessing != null) {
                    $this->resprocessing->setOutcomes($this->outcomes);
                }
                $this->outcomes = null;
                break;
            case "decvar":
                if ($this->outcomes != null) {
                    $this->outcomes->addDecvar($this->decvar);
                }
                $this->decvar = null;
                break;
            case "presentation":
                $this->in_presentation = false;
                if ($this->presentation != null && $this->item != null) {
                    $this->item->setPresentation($this->presentation);
                }
                $this->presentation = null;
                break;
            case "response_label":
                if ($this->render_type != null) {
                    $this->render_type->addResponseLabel($this->response_label);
                    $this->response_label = null;
                }
                break;
            case "render_choice":
            case "render_hotspot":
            case "render_fib":
                if ($this->in_response) {
                    if ($this->response != null) {
                        if ($this->render_type != null) {
                            $this->response->setRenderType($this->render_type);
                            $this->render_type = null;
                        }
                    }
                }
                break;
            case "response_lid":
            case "response_xy":
            case "response_str":
            case "response_num":
            case "response_grp":
                $this->gap_index++;
                if ($this->presentation != null) {
                    if ($this->response != null) {
                        $this->presentation->addResponse($this->response);
                        if ($this->item != null) {
                            $this->item->addPresentationitem($this->response);
                        }
                    }
                }
                $this->response = null;
                $this->in_response = false;
                break;
            case "item":
                if ($this->do_nothing) {
                    $this->do_nothing = false;
                    return;
                }
                if ((string) $this->item->getQuestionType() !== '') {
                    // this is an ILIAS QTI question
                } else {
                    // this is a QTI question which wasn't generated by ILIAS
                }
                global $ilDB;
                global $ilUser;
                // save the item directly to save memory
                // the database id's of the created items are exported. if the import fails
                // ILIAS can delete the already imported items

                // problems: the object id of the parent questionpool is not yet known. must be set later
                //           the complete flag must be calculated?
                $qt = $this->item->determineQuestionType();
                $presentation = $this->item->getPresentation();

                if (!ilAssQuestionTypeList::isImportable($qt)) {
                    return;
                }
                assQuestion::_includeClass($qt);
                $question = new $qt();
                $fbt = str_replace('ass', 'ilAss', $qt) . 'Feedback';
                $question->feedbackOBJ = new $fbt(
                    $question,
                    $GLOBALS['ilCtrl'],
                    $GLOBALS['ilDB'],
                    $GLOBALS['lng']
                );
                $this->import_mapping = $question->fromXML(
                    $this->item,
                    $this->qpl_id,
                    $this->tst_id,
                    $this->tst_object,
                    $this->question_counter,
                    $this->import_mapping,
                    $this->solutionhints
                );

                $this->solutionhints = [];

                $this->numImportedItems++;

                break;
            case "material":
                if ($this->material) {
                    $mat = $this->material->getMaterial(0);
                    if(!is_array($mat)) {
                        $this->material = null;
                        break;
                    }
                    if ($mat["type"] === "mattext" && $mat["material"]->getLabel() === "suggested_solution") {
                        $this->item->addSuggestedSolution($mat["material"], $this->gap_index);
                    }
                    if ($this->in_objectives) {
                        $this->objectives->addMaterial($this->material);
                    } elseif ($this->render_type != null && strtolower($this->getParent($a_xml_parser)) === "render_hotspot") {
                        $this->render_type->addMaterial($this->material);
                    } elseif (count($this->flow_mat) && strtolower($this->getParent($a_xml_parser)) === "flow_mat") {
                        $this->flow_mat[count($this->flow_mat) - 1]->addMaterial($this->material);
                    } elseif ($this->itemfeedback != null) {
                        $this->itemfeedback->addMaterial($this->material);
                    } elseif ($this->response_label != null) {
                        $this->response_label->addMaterial($this->material);
                    } elseif ($this->response != null) {
                        if ($this->response->hasRendering()) {
                            $this->response->setMaterial2($this->material);
                        } else {
                            $this->response->setMaterial1($this->material);
                        }
                    } elseif (($this->in_presentation) && (!$this->in_response)) {
                        if (!is_object($this->item->getQuestiontext())) {
                            $this->item->setQuestiontext($this->material);
                        }
                        $this->presentation->addMaterial($this->material);
                    } elseif ($this->presentation != null) {
                        $this->presentation->addMaterial($this->material);
                        if ($this->item != null) {
                            $this->item->addPresentationitem($this->material);
                        }
                    }
                }
                $this->material = null;
                break;
            case "matimage":

                if (!$this->isMatImageAvailable()) {
                    break;
                }

                if ($this->virusDetected($this->matimage->getRawContent())) {
                    break;
                }
                try {
                    $matImageSecurity = new ilQtiMatImageSecurity($this->matimage);
                    $matImageSecurity->sanitizeLabel();
                } catch (Exception $e) {
                    break;
                }
                if (!$matImageSecurity->validate()) {
                    break;
                }

                $this->material->addMatimage($this->matimage);
                $this->matimage = null;
                break;

            // add support for matbreak element
            case "matbreak":
                $this->mattext = new ilQTIMattext();
                $this->mattext->setContent('<br />');
                $this->material->addMattext($this->mattext);
                $this->mattext = null;
                break;
            case "resprocessing":
                if ($this->item != null) {
                    $this->item->addResprocessing($this->resprocessing);
                }
                $this->resprocessing = null;
                break;
            case "mattext":
                if ($this->material != null) {
                    $this->material->addMattext($this->mattext);
                }
                $this->mattext = null;
                break;
            case "matapplet":
                if ($this->material != null) {
                    $this->material->addMatapplet($this->matapplet);
                }
                $this->matapplet = null;
                break;

            case assQuestionExport::ITEM_SOLUTIONHINT:
                $this->solutionhint['txt'] = $this->characterbuffer;
                $this->solutionhints[] = $this->solutionhint;
                break;
        }
        $this->depth[$a_xml_parser] -= 1; // Issue with SplObjectStorage: Cannot use --.
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        switch ($this->parser_mode) {
            case self::IL_MO_PARSE_QTI:
                $this->handlerParseCharacterData($a_xml_parser, $a_data);
                break;
            case self::IL_MO_VERIFY_QTI:
                $this->handlerVerifyCharacterData($a_xml_parser, $a_data);
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerParseCharacterData($a_xml_parser, string $a_data): void
    {
        if ($this->do_nothing) {
            return;
        }
        $this->characterbuffer .= $a_data;
        $a_data = $this->characterbuffer;
        switch ($this->qti_element) {
            case "fieldlabel":
                $this->metadata["label"] = $a_data;
                break;
            case "fieldentry":
                $this->metadata["entry"] = $a_data;
                break;
            case "response_label":
                if ($this->response_label != null) {
                    $this->response_label->setContent($a_data);
                }
                break;
            case "setvar":
                if ($this->setvar != null) {
                    $this->setvar->setContent($a_data);
                }
                break;
            case "displayfeedback":
                if ($this->displayfeedback != null) {
                    $this->displayfeedback->setContent($a_data);
                }
                break;
            case "varequal":
            case "varlt":
            case "varlte":
            case "vargt":
            case "vargte":
            case "varsubset":
            case "varinside":
            case "varsubstring":
                if ($this->responsevar != null) {
                    $this->responsevar->setContent($a_data);
                }
                break;
            case "decvar":
                if (strlen($a_data)) {
                    if ($this->decvar != null) {
                        $this->decvar->setContent($a_data);
                    }
                }
                break;
            case "mattext":
                if ($this->mattext != null) {
                    $this->mattext->setContent($a_data);
                }
                break;
            case "matapplet":
                if ($this->matapplet != null) {
                    $this->matapplet->setContent($a_data);
                }
                break;
            case "matimage":
                if ($this->matimage != null) {
                    $this->matimage->setContent($a_data);
                }
                break;
            case "duration":
                switch ($this->getParent($a_xml_parser)) {
                    case "assessment":
                        // to be done
                        break;
                    case "section":
                        // to be done
                        break;
                    case "item":
                        $this->item->setDuration($a_data);
                        break;
                }
                break;
            case "qticomment":
                switch ($this->getParent($a_xml_parser)) {
                    case "item":
                        $this->item->setComment($a_data);
                        break;
                    case "assessment":
                        $this->assessment->setComment($a_data);
                        break;
                    default:
                        break;
                }
                break;
        }
        $this->sametag = true;
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param array<string, string> $a_attribs
     */
    public function handlerVerifyBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        $this->qti_element = $a_name;

        switch (strtolower($a_name)) {
            case "assessment":
                $this->assessment = $this->assessments[] = new ilQTIAssessment();
                $this->in_assessment = true;
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "title":
                            $this->assessment->setTitle($value);
                            break;
                        case "ident":
                            $this->assessment->setIdent($value);
                            break;
                    }
                }
                break;
            case "questestinterop":
                $this->verifyroot = true;
                break;
            case "qtimetadatafield":
                $this->metadata = ["label" => "", "entry" => ""];
                $this->verifymetadatafield = 1;
                break;
            case "fieldlabel":
                $this->verifyfieldlabeltext = "";
                if ($this->verifymetadatafield == 1) {
                    $this->verifyfieldlabel = 1;
                }
                break;
            case "fieldentry":
                $this->verifyfieldentrytext = "";
                if ($this->verifymetadatafield == 1) {
                    $this->verifyfieldentry = 1;
                }
                break;
            case "item":
                $title = "";
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "title":
                            $title = $value;
                            break;
                    }
                }
                $this->founditems[] = ["title" => "$title", "type" => "", "ident" => $a_attribs["ident"]];
                break;
            case "response_lid":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    // test for non ILIAS generated question types
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "rcardinality":
                                switch (strtolower($value)) {
                                    case "single":
                                        $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_MULTIPLE_CHOICE_SR;
                                        break;
                                    case "multiple":
                                        $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_MULTIPLE_CHOICE_MR;
                                        break;
                                    case "ordered":
                                        $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_ORDERING;
                                        break;
                                }
                                break;
                        }
                    }
                }
                break;

            case assQuestionExport::ITEM_SOLUTIONHINT:
                $this->solutionhint = array_map('intval', $a_attribs);
                $this->solutionhint['txt'] = '';
                break;
            case "response_str":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    // test for non ILIAS generated question types
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "rcardinality":
                                switch (strtolower($value)) {
                                    case "single":
                                        $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_CLOZE;
                                        break;
                                    case "ordered":
                                        $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_TEXT;
                                        break;
                                }
                                break;
                        }
                    }
                }
                break;
            case "response_xy":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_IMAGEMAP;
                }
                break;
            case "response_num":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_NUMERIC;
                }
                break;
            case "response_grp":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = ilQTIItem::QT_MATCHING;
                }
                break;
            case "qticomment":
                // check for "old" ILIAS qti format (not well formed)
                $this->verifyqticomment = 1;
                break;
            case "presentation":
                foreach ($a_attribs as $attribute => $value) {
                    switch (strtolower($attribute)) {
                        case "label":
                            $this->founditems[count($this->founditems) - 1]["title"] = $value;
                            break;
                    }
                }
                break;

        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerVerifyEndTag($a_xml_parser, string $a_name): void
    {
        switch (strtolower($a_name)) {
            case "assessment":
                foreach ($this->assessment->qtimetadata as $metaField) {
                    if ($metaField['label'] == 'question_set_type') {
                        $this->setQuestionSetType($metaField['entry']);
                        break;
                    }

                    if ($metaField['label'] == 'random_test') {
                        if ($metaField['entry'] == 1) {
                            $this->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
                        } else {
                            $this->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
                        }
                        break;
                    }
                }
                $this->in_assessment = false;
                break;
            case "qticomment":
                // check for "old" ILIAS qti format (not well formed)
                $this->verifyqticomment = 0;
                break;
            case "qtimetadatafield":
                $this->verifymetadatafield = 0;
                if ($this->verifyfieldlabeltext === "QUESTIONTYPE") {
                    $this->founditems[count($this->founditems) - 1]["type"] = $this->verifyfieldentrytext;
                }
                if ($this->in_assessment) {
                    $this->assessment->addQtiMetadata($this->metadata);
                }
                $this->metadata = ["label" => "", "entry" => ""];
                break;
            case "fieldlabel":
                $this->verifyfieldlabel = 0;
                break;
            case "fieldentry":
                $this->verifyfieldentry = 0;
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerVerifyCharacterData($a_xml_parser, string $a_data): void
    {
        if ($this->verifyqticomment == 1) {
            if (preg_match("/Questiontype\=(.*)/", $a_data, $matches)) {
                if (count($this->founditems)) {
                    $this->founditems[count($this->founditems) - 1]["type"] = $matches[1];
                }
            }
        } elseif ($this->verifyfieldlabel == 1) {
            $this->verifyfieldlabeltext = $a_data;
        } elseif ($this->verifyfieldentry == 1) {
            $this->verifyfieldentrytext = $a_data;
        }

        switch ($this->qti_element) {
            case "fieldlabel":
                $this->metadata["label"] = $a_data;
                break;
            case "fieldentry":
                $this->metadata["entry"] = $a_data;
                break;
        }
    }

    /**
     * @return array{title: string, type: string, ident: string}[]
     */
    public function &getFoundItems(): array
    {
        return $this->founditems;
    }

    /**
     * Get array of new created questions for import id.
     * @return array<string, {test: mixed}>
     */
    public function getImportMapping(): array
    {
        return $this->import_mapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuestionIdMapping(): array
    {
        $questionIdMapping = [];

        foreach ($this->getImportMapping() as $k => $v) {
            $oldQuestionId = substr($k, strpos($k, 'qst_') + strlen('qst_'));
            $newQuestionId = $v['test']; // yes, this is the new question id ^^

            $questionIdMapping[$oldQuestionId] = $newQuestionId;
        }

        return $questionIdMapping;
    }

    public function setXMLContent(string $a_xml_content): void
    {
        $a_xml_content = $this->cleanInvalidXmlChars($a_xml_content);

        parent::setXMLContent($a_xml_content);
    }

    /**
     * @inheritdoc
     */
    protected function openXMLFile()
    {
        $xmlContent = file_get_contents($this->xml_file);
        $xmlContent = $this->cleanInvalidXmlChars($xmlContent);
        file_put_contents($this->xml_file, $xmlContent);

        return parent::openXMLFile();
    }

    protected function fetchNumericVersionFromVersionDateString(string $versionDateString): ?string
    {
        $matches = null;

        if (preg_match('/^(\d+\.\d+\.\d+) .*$/', $versionDateString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function fetchSourceNicFromItemIdent(string $itemIdent): ?string
    {
        $matches = null;

        if (preg_match('/^il_(\d+?)_qst_\d+$/', $itemIdent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function cleanInvalidXmlChars(string $xmlContent): string
    {
        // http://www.w3.org/TR/xml/#charsets

        // DOES ACTUALLY KILL CONTENT, SHOULD CLEAN NON ESCAPED ILLEGAL CHARS, DON'T KNOW
        //$reg = '/[^\x09\x0A\x0D\x20-\uD7FF\uE000-\uFFFD\u10000-\u10FFFF]/';
        //$xmlContent = preg_replace($reg, '', $xmlContent);

        // remove illegal chars escaped to html entities
        $needles = [];
        for ($i = 0x00, $max = 0x08; $i <= $max; $i += 0x01) {
            $needles[] = "&#{$i};";
        }
        for ($i = 0x0b, $max = 0x0c; $i <= $max; $i += 0x01) {
            $needles[] = "&#{$i};";
        }
        for ($i = 0x0e, $max = 0x1f; $i <= $max; $i += 0x01) {
            $needles[] = "&#{$i};";
        }
        for ($i = 0xd800, $max = 0xdfff; $i <= $max; $i += 0x0001) {
            $needles[] = "&#{$i};";
        }
        for ($i = 0xfffe, $max = 0xffff; $i <= $max; $i += 0x0001) {
            $needles[] = "&#{$i};";
        }
        $reg = '/(' . implode('|', $needles) . ')/';
        $xmlContent = preg_replace($reg, '', $xmlContent);

        return $xmlContent;
    }

    public function getNumImportedItems(): int
    {
        return $this->numImportedItems;
    }

    protected function isMatImageAvailable(): bool
    {
        if (!$this->material) {
            return false;
        }

        if (!$this->matimage) {
            return false;
        }

        return true;
    }

    protected function virusDetected(string $buffer): bool
    {
        $vs = ilVirusScannerFactory::_getInstance();

        if ($vs === null) {
            return false; // no virus scan, no virus detected
        }

        return $vs->scanBuffer($buffer);
    }

    private function assessmentBeginTag(array $a_attribs): void
    {
        $this->assessment = $this->assessments[] = new ilQTIAssessment();
        $this->in_assessment = true;
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "title":
                    $this->assessment->setTitle($value);
                    break;
                case "ident":
                    $this->assessment->setIdent($value);
                    break;
            }
        }
    }

    private function assessmentControlBeginTag(array $a_attribs): void
    {
        $this->assessmentcontrol = new ilQTIAssessmentcontrol();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "solutionswitch":
                    $this->assessmentcontrol->setSolutionswitch($value);
                    break;
                case "hintswitch":
                    $this->assessmentcontrol->setHintswitch($value);
                    break;
                case "feedbackswitch":
                    $this->assessmentcontrol->setFeedbackswitch($value);
                    break;
            }
        }
    }

    private function itemFeedbackBeginTag(array $a_attribs): void
    {
        $this->itemfeedback = new ilQTIItemfeedback();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "ident":
                    $this->itemfeedback->setIdent($value);
                    break;
                case "view":
                    $this->itemfeedback->setView($value);
                    break;
            }
        }
    }

    private function displayFeedbackBeginTag(array $a_attribs): void
    {
        $this->displayfeedback = new ilQTIDisplayfeedback();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "feedbacktype":
                    $this->displayfeedback->setFeedbacktype($value);
                    break;
                case "linkrefid":
                    $this->displayfeedback->setLinkrefid($value);
                    break;
            }
        }
    }

    private function setVarBeginTag(array $a_attribs): void
    {
        $this->setvar = new ilQTISetvar();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "action":
                    $this->setvar->setAction($value);
                    break;
                case "varname":
                    $this->setvar->setVarname($value);
                    break;
            }
        }
    }

    private function varEqualBeginTag(array $a_attribs): void
    {
        $this->responsevar = new ilQTIResponseVar(ilQTIResponseVar::RESPONSEVAR_EQUAL);
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "case":
                    $this->responsevar->setCase($value);
                    break;
                case "respident":
                    $this->responsevar->setRespident($value);
                    break;
                case "index":
                    $this->responsevar->setIndex($value);
                    break;
            }
        }
    }

    private function termsAndDefinitionsBeginTag(string $a_name, array $a_attribs): void
    {
        $response_type = 0;
        switch (strtolower($a_name)) {
            case "response_lid":
                $response_type = ilQTIResponse::RT_RESPONSE_LID;
                break;
            case "response_xy":
                $response_type = ilQTIResponse::RT_RESPONSE_XY;
                break;
            case "response_str":
                $response_type = ilQTIResponse::RT_RESPONSE_STR;
                break;
            case "response_num":
                $response_type = ilQTIResponse::RT_RESPONSE_NUM;
                break;
            case "response_grp":
                $response_type = ilQTIResponse::RT_RESPONSE_GRP;
                break;
        }
        $this->in_response = true;
        $this->response = new ilQTIResponse($response_type);
        $this->response->setFlow($this->flow);
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "ident":
                    $this->response->setIdent($value);
                    break;
                case "rtiming":
                    $this->response->setRTiming($value);
                    break;
                case "rcardinality":
                    $this->response->setRCardinality($value);
                    break;
                case "numtype":
                    $this->response->setNumtype($value);
                    break;
            }
        }
    }

    private function itemBeginTag(array $a_attribs): void
    {
        $this->gap_index = 0;
        $this->item = $this->items[] = new ilQTIItem();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "ident":
                    $this->item->setIdent($value);
                    $this->item->setIliasSourceNic(
                        $this->fetchSourceNicFromItemIdent($value)
                    );
                    if ($this->isIgnoreItemsEnabled()) {
                        $this->do_nothing = true;
                    } elseif (count($this->import_idents) > 0) {
                        if (!in_array($value, $this->import_idents)) {
                            $this->do_nothing = true;
                        }
                    }
                    break;
                case "title":
                    $this->item->setTitle($value);
                    break;
                case "maxattempts":
                    $this->item->setMaxattempts($value);
                    break;
            }
        }
    }

    private function resprocessingBeginTag(array $a_attribs): void
    {
        $this->resprocessing = new ilQTIResprocessing();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "scoremodel":
                    $this->resprocessing->setScoremodel($value);
                    break;
            }
        }
    }

    private function renderFibBeginTag(array $a_attribs): void
    {
        if (!$this->in_response) {
            return;
        }
        $this->render_type = new ilQTIRenderFib();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "encoding":
                    $this->render_type->setEncoding($value);
                    break;
                case "fibtype":
                    $this->render_type->setFibtype($value);
                    break;
                case "rows":
                    $this->render_type->setRows($value);
                    break;
                case "maxchars":
                    $this->render_type->setMaxchars($value);
                    break;
                case "prompt":
                    $this->render_type->setPrompt($value);
                    break;
                case "columns":
                    $this->render_type->setColumns($value);
                    break;
                case "charset":
                    $this->render_type->setCharset($value);
                    break;
                case "maxnumber":
                    $this->render_type->setMaxnumber($value);
                    break;
                case "minnumber":
                    $this->render_type->setMinnumber($value);
                    break;
            }
        }
    }

    private function renderHotspotBeginTag(array $a_attribs): void
    {
        if (!$this->in_response) {
            return;
        }
        $this->render_type = new ilQTIRenderHotspot();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "showdraw":
                    $this->render_type->setShowdraw($value);
                    break;
                case "minnumber":
                    $this->render_type->setMinnumber($value);
                    break;
                case "maxnumber":
                    $this->render_type->setMaxnumber($value);
                    break;
            }
        }
    }

    private function renderChoiceBeginTag(array $a_attribs): void
    {
        if (!$this->in_response) {
            return;
        }
        $this->render_type = new ilQTIRenderChoice();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "shuffle":
                    $this->render_type->setShuffle($value);
                    break;
                case 'minnumber':
                    $this->render_type->setMinnumber($value);
                    break;
                case 'maxnumber':
                    $this->render_type->setMaxnumber($value);
                    break;
            }
        }
    }

    private function responseLabelBeginTag(array $a_attribs): void
    {
        if ($this->render_type == null) {
            return;
        }
        $this->response_label = new ilQTIResponseLabel();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "rshuffle":
                    $this->response_label->setRshuffle($value);
                    break;
                case "rarea":
                    $this->response_label->setRarea($value);
                    break;
                case "rrange":
                    $this->response_label->setRrange($value);
                    break;
                case "labelrefid":
                    $this->response_label->setLabelrefid($value);
                    break;
                case "ident":
                    $this->response_label->setIdent($value);
                    break;
                case "match_group":
                    $this->response_label->setMatchGroup($value);
                    break;
                case "match_max":
                    $this->response_label->setMatchMax($value);
                    break;
            }
        }
    }

    private function matAppletBeginTag(array $a_attribs): void
    {
        $this->matapplet = new ilQTIMatapplet();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "label":
                    $this->matapplet->setLabel($value);
                    break;
                case "uri":
                    $this->matapplet->setUri($value);
                    break;
                case "y0":
                    $this->matapplet->setY0($value);
                    break;
                case "height":
                    $this->matapplet->setHeight($value);
                    break;
                case "width":
                    $this->matapplet->setWidth($value);
                    break;
                case "x0":
                    $this->matapplet->setX0($value);
                    break;
                case "embedded":
                    $this->matapplet->setEmbedded($value);
                    break;
                case "entityref":
                    $this->matapplet->setEntityref($value);
                    break;
            }
        }
    }

    private function matTextBeginTag(array $a_attribs): void
    {
        $this->mattext = new ilQTIMattext();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "texttype":
                    $this->mattext->setTexttype($value);
                    break;
                case "label":
                    $this->mattext->setLabel($value);
                    break;
                case "charset":
                    $this->mattext->setCharset($value);
                    break;
                case "uri":
                    $this->mattext->setUri($value);
                    break;
                case "xml:space":
                    $this->mattext->setXmlspace($value);
                    break;
                case "xml:lang":
                    $this->mattext->setXmllang($value);
                    break;
                case "entityref":
                    $this->mattext->setEntityref($value);
                    break;
                case "height":
                    $this->mattext->setHeight($value);
                    break;
                case "width":
                    $this->mattext->setWidth($value);
                    break;
                case "x0":
                    $this->mattext->setX0($value);
                    break;
                case "y0":
                    $this->mattext->setY0($value);
                    break;
            }
        }
    }

    private function materialBeginTag(array $a_attribs): void
    {
        $this->material = new ilQTIMaterial();
        $this->material->setFlow($this->flow);
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "label":
                    $this->material->setLabel($value);
                    break;
            }
        }
    }

    private function matImageBeginTag(array $a_attribs): void
    {
        $this->matimage = new ilQTIMatimage();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "imagtype":
                    $this->matimage->setImagetype($value);
                    break;
                case "label":
                    $this->matimage->setLabel($value);
                    break;
                case "height":
                    $this->matimage->setHeight($value);
                    break;
                case "width":
                    $this->matimage->setWidth($value);
                    break;
                case "uri":
                    $this->matimage->setUri($value);
                    break;
                case "embedded":
                    $this->matimage->setEmbedded($value);
                    break;
                case "x0":
                    $this->matimage->setX0($value);
                    break;
                case "y0":
                    $this->matimage->setY0($value);
                    break;
                case "entityref":
                    $this->matimage->setEntityref($value);
                    break;
            }
        }
        if (!$this->matimage->getEmbedded() && strlen($this->matimage->getUri())) {
            $this->matimage->setContent(@file_get_contents(dirname($this->xml_file) . '/' . $this->matimage->getUri()));
        }
    }

    private function decVarBeginTag(array $a_attribs): void
    {
        $this->decvar = new ilQTIDecvar();
        foreach ($a_attribs as $attribute => $value) {
            switch (strtolower($attribute)) {
                case "varname":
                    $this->decvar->setVarname($value);
                    break;
                case "vartype":
                    $this->decvar->setVartype($value);
                    break;
                case "defaultval":
                    $this->decvar->setDefaultval($value);
                    break;
                case "minvalue":
                    $this->decvar->setMinvalue($value);
                    break;
                case "maxvalue":
                    $this->decvar->setMaxvalue($value);
                    break;
                case "members":
                    $this->decvar->setMembers($value);
                    break;
                case "cutvalue":
                    $this->decvar->setCutvalue($value);
                    break;
            }
        }
    }
}
