<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

include_once("./Services/Xml/classes/class.ilSaxParser.php");
include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/class.ilAssQuestionTypeList.php';

const IL_MO_PARSE_QTI = 1;
const IL_MO_VERIFY_QTI = 2;

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
    /**
     * @var bool
     */
    public $hasRootElement;

    /**
     * @var array<int, string>
     */
    public $path;

    /**
     * @var ilQTIItem[]
     */
    public $items;

    /**
     * @var ilQTIItem|null
     */
    public $item;

    /**
     * @var ilQTIXMLParserArray
     */
    public $depth;

    /**
     * @var string
     */
    public $qti_element;

    /**
     * @var bool
     */
    public $in_presentation;

    /**
     * @var bool
     */
    public $in_response;

    /**
     * @var ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public $render_type;

    /**
     * @var ilQTIResponseLabel|null
     */
    public $response_label;

    /**
     * @var ilQTIMaterial|null
     */
    public $material;

    /**
     * @var ilQTIMatimage
     */
    public $matimage;

    /**
     * @var ilQTIResponse|null
     */
    public $response;

    /**
     * @var ilQTIResprocessing|null
     */
    public $resprocessing;

    /**
     * @var ilQTIOutcomes|null
     */
    public $outcomes;

    /**
     * @var ilQTIDecvar|null
     */
    public $decvar;

    /**
     * @var ilQTIRespcondition|null
     */
    public $respcondition;

    /**
     * @var ilQTISetvar|null
     */
    public $setvar;

    /**
     * @var ilQTIDisplayfeedback|null
     */
    public $displayfeedback;

    /**
     * @var ilQTIItemfeedback|null
     */
    public $itemfeedback;

    /**
     * @var ilQTIFlowMat[]
     */
    public $flow_mat;

    /**
     * @var int
     */
    public $flow;

    /**
     * @var ilQTIPresentation|null
     */
    public $presentation;

    /**
     * @var ilQTIMattext|null
     */
    public $mattext;

    /** @var bool|null */
    public $sametag;

    /**
     * @var string|null
     */
    public $characterbuffer;

    /**
     * @var ilQTIConditionvar|null
     */
    public $conditionvar;

    /**
     * @var int
     */
    public $parser_mode;

    /**
     * @var string[]
     */
    public $import_idents;

    /**
     * @var int
     */
    public $qpl_id;

    /**
     * @var int|null
     */
    public $tst_id;

    /**
     * @var ilObjTest|null
     */
    public $tst_object;

    /**
     * @var bool
     */
    public $do_nothing;

    /**
     * @var int
     */
    public $gap_index;

    /**
     * @var ilQTIAssessment[]
     */
    public $assessments;

    /**
     * @var ilQTIAssessment|null
     */
    public $assessment;

    /**
     * @var ilQTIAssessmentcontrol|null
     */
    public $assessmentcontrol;

    /**
     * @var ilQTIObjectives|null
     */
    public $objectives;

    public bool $in_assessment;

    /**
     * @var ilQTISection|null
     */
    public $section;

    /**
     * @var array<string, ['test' => mixed]>
     */
    public $import_mapping;

    public int $question_counter;

    /**
     * @var bool|null
     */
    public $in_itemmetadata;

    public bool $in_objectives;

    /**
     * @var array{title: string, type: string, ident: string}[]
     */
    public array $founditems;

    public bool $verifyroot;

    public int $verifyqticomment;

    public int $verifymetadatafield = 0;

    public int $verifyfieldlabel;

    public string $verifyfieldlabeltext;

    public int $verifyfieldentry;

    public string $verifyfieldentrytext;

    /**
     * @var int
     */
    protected $numImportedItems = 0;

    /**
     * @var ilQTIPresentationMaterial
     */
    protected $prensentation_material;
    /**
     * @var bool
     */
    protected $in_prensentation_material = false;

    /**
     * @var bool
     */
    protected $ignoreItemsEnabled = false;

    /**
     * @var false
     */
    protected bool $in_reponse;

    /**
     * @var null
     */
    private $render_hotspot;

    /**
     * @var null
     */
    private $matapplet;

    /**
     * @var array{label: string, entry: string}
     */
    private array $metadata;
    private ?ilQTIResponseVar $responsevar;

    /**
     * @var string|null
     */
    protected $questionSetType;

    public function isIgnoreItemsEnabled() : bool
    {
        return $this->ignoreItemsEnabled;
    }

    public function setIgnoreItemsEnabled(bool $ignoreItemsEnabled) : void
    {
        $this->ignoreItemsEnabled = $ignoreItemsEnabled;
    }

    /**
     * Constructor
     *
     * @param	string		$a_xml_file			xml file
     * @param  integer $a_mode Parser mode IL_MO_PARSE_QTI | IL_MO_VERIFY_QTI
     * @access	public
     */
    //  TODO: The following line gets me an parse error in PHP 4, but I found no hint that pass-by-reference is forbidden in PHP 4 ????
    public function __construct(?string $a_xml_file, $a_mode = IL_MO_PARSE_QTI, $a_qpl_id = 0, $a_import_idents = "")
    {
        global $lng;

        $this->setParserMode($a_mode);

        parent::__construct($a_xml_file);

        $this->qpl_id = $a_qpl_id;
        $this->import_idents = array();
        if (is_array($a_import_idents)) {
            $this->import_idents = &$a_import_idents;
        }

        $this->lng = &$lng;
        $this->hasRootElement = false;
        $this->import_mapping = array();
        $this->assessments = array();
        $this->assessment = null;
        $this->section = null;
        $this->path = array();
        $this->items = array();
        $this->item = null;
        $this->depth = new ilQTIXMLParserArray();
        $this->do_nothing = false;
        $this->qti_element = "";
        $this->in_presentation = false;
        $this->in_objectives = false;
        $this->in_reponse = false;
        $this->render_type = null;
        $this->render_hotspot = null;
        $this->response_label = null;
        $this->material = null;
        $this->response = null;
        $this->assessmentcontrol = null;
        $this->objectives = null;
        $this->matimage = null;
        $this->resprocessing = null;
        $this->outcomes = null;
        $this->decvar = null;
        $this->respcondition = null;
        $this->setvar = null;
        $this->displayfeedback = null;
        $this->itemfeedback = null;
        $this->flow_mat = array();
        $this->question_counter = 1;
        $this->flow = 0;
        $this->gap_index = 0;
        $this->presentation = null;
        $this->mattext = null;
        $this->matapplet = null;
        $this->sametag = false;
        $this->in_assessment = false;
        $this->characterbuffer = "";
        $this->metadata = array("label" => "", "entry" => "");
    }

    /**
     * @return null|string
     */
    public function getQuestionSetType()
    {
        return $this->questionSetType;
    }

    /**
     * @param null|string $questionSetType
     */
    public function setQuestionSetType($questionSetType) : void
    {
        $this->questionSetType = $questionSetType;
    }

    /**
     * @param ilObjTest
     */
    public function setTestObject(&$a_tst_object) : void
    {
        $this->tst_object = &$a_tst_object;
        if (is_object($a_tst_object)) {
            $this->tst_id = $this->tst_object->getId();
        }
    }

    /**
     * @param int $a_mode
     */
    public function setParserMode($a_mode = IL_MO_PARSE_QTI) : void
    {
        $this->parser_mode = $a_mode;
        $this->founditems = array();
        $this->verifyroot = false;
        $this->verifyqticomment = 0;
        $this->verifymetadatafield = 0;
        $this->verifyfieldlabel = 0;
        $this->verifyfieldentry = 0;
        $this->verifyfieldlabeltext = "";
        $this->verifyfieldentrytext = "";
        $this->question_counter = 1;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    *
    * @param XMLParser $a_xml_parser
    */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function startParsing() : void
    {
        $this->question_counter = 1;
        parent::startParsing();
    }

    /**
     * @param XMLParser
     * @return string
     */
    public function getParent($a_xml_parser)
    {
        if ($this->depth[$a_xml_parser] > 0) {
            return $this->path[$this->depth[$a_xml_parser] - 1];
        }

        return "";
    }

    /**
     * @param array<string, string> $a_attribs
     */
    public function handlerBeginTag(XMLParser $a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($this->parser_mode) {
            case IL_MO_PARSE_QTI:
                $this->handlerParseBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case IL_MO_VERIFY_QTI:
                $this->handlerVerifyBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
        }
    }

    /**
     * @param array<string, string> $a_attribs
     */
    public function handlerParseBeginTag(XMLParser $a_xml_parser, string $a_name, array $a_attribs) : void
    {
        if ($this->do_nothing) {
            return;
        }
        $this->sametag = false;
        $this->characterbuffer = "";
        $this->depth[$a_xml_parser]++;
        $this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
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
            case "assessmentcontrol":
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
                $this->metadata = array("label" => "", "entry" => "");
                break;
            case "flow":
                $this->flow++;
                break;
            case "flow_mat":
                $this->flow_mat[] = new ilQTIFlowMat();
                break;
            case "itemfeedback":
                $this->itemfeedback = new ilQTIItemfeedback();
                if (is_array($a_attribs)) {
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
                break;
            case "displayfeedback":
                $this->displayfeedback = new ilQTIDisplayfeedback();
                if (is_array($a_attribs)) {
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
                break;
            case "setvar":
                $this->setvar = new ilQTISetvar();
                if (is_array($a_attribs)) {
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
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_EQUAL);
                if (is_array($a_attribs)) {
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
                break;
            case "varlt":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_LT);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "varlte":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_LTE);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "vargt":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_GT);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "vargte":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_GTE);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "varsubset":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_SUBSET);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "varinside":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_INSIDE);
                if (is_array($a_attribs)) {
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
                }
                break;
            case "varsubstring":
                $this->responsevar = new ilQTIResponseVar(RESPONSEVAR_SUBSTRING);
                if (is_array($a_attribs)) {
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
                break;
            case "respcondition":
                $this->respcondition = new ilQTIRespcondition();
                if (is_array($a_attribs)) {
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
                }
                break;
            case "outcomes":
                $this->outcomes = new ilQTIOutcomes();
                break;
            case "decvar":
                $this->decvar = new ilQTIDecvar();
                if (is_array($a_attribs)) {
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
                break;
            case "matimage":
                $this->matimage = new ilQTIMatimage();
                if (is_array($a_attribs)) {
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
                }
                if (!$this->matimage->getEmbedded() && strlen($this->matimage->getUri())) {
                    $this->matimage->setContent(@file_get_contents(dirname($this->xml_file) . '/' . $this->matimage->getUri()));
                }
                break;
            case "material":
                $this->material = new ilQTIMaterial();
                $this->material->setFlow($this->flow);
                if (is_array($a_attribs)) {
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "label":
                                $this->material->setLabel($value);
                                break;
                        }
                    }
                }
                break;
            case "mattext":
                $this->mattext = new ilQTIMattext();
                if (is_array($a_attribs)) {
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
                break;
            case "matapplet":
                $this->matapplet = new ilQTIMatapplet();
                if (is_array($a_attribs)) {
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
                if ($this->render_type != null) {
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
                break;
            case "render_choice":
                if ($this->in_response) {
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
                break;
            case "render_hotspot":
                if ($this->in_response) {
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
                break;
            case "render_fib":
                if ($this->in_response) {
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
                $response_type = "0";//@todo maybe null.
                switch (strtolower($a_name)) {
                    case "response_lid":
                        $response_type = RT_RESPONSE_LID;
                        break;
                    case "response_xy":
                        $response_type = RT_RESPONSE_XY;
                        break;
                    case "response_str":
                        $response_type = RT_RESPONSE_STR;
                        break;
                    case "response_num":
                        $response_type = RT_RESPONSE_NUM;
                        break;
                    case "response_grp":
                        $response_type = RT_RESPONSE_GRP;
                        break;
                }
                $this->in_response = true;
                $this->response = new ilQTIResponse($response_type);
                $this->response->setFlow($this->flow);
                if (is_array($a_attribs)) {
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
                break;
            case "item":
                $this->gap_index = 0;
                $this->item = $this->items[] = new ilQTIItem();
                if (is_array($a_attribs)) {
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
                break;
            case "resprocessing":
                $this->resprocessing = new ilQTIResprocessing();
                if (is_array($a_attribs)) {
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "scoremodel":
                                $this->resprocessing->setScoremodel($value);
                                break;
                        }
                    }
                }
                break;
        }
    }

    public function handlerEndTag(XMLParser $a_xml_parser, string $a_name) : void
    {
        switch ($this->parser_mode) {
            case IL_MO_PARSE_QTI:
                $this->handlerParseEndTag($a_xml_parser, $a_name);
                break;
            case IL_MO_VERIFY_QTI:
                $this->handlerVerifyEndTag($a_xml_parser, $a_name);
                break;
        }
    }

    /**
     * @noinspection NotOptimalIfConditionsInspection
     */
    public function handlerParseEndTag(XMLParser $a_xml_parser, string $a_name) : void
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
                $this->metadata = array("label" => "", "entry" => "");
                break;
            case "flow":
                $this->flow--;
                break;
            case "flow_mat":
                if (count($this->flow_mat)) {
                    $flow_mat = array_pop($this->flow_mat);
                    if (count($this->flow_mat)) {
                        $this->flow_mat[count($this->flow_mat) - 1]->addFlow_mat($flow_mat);
                    } elseif ($this->in_prensentation_material) {
                        $this->prensentation_material->addFlowMat($flow_mat);
                    } elseif ($this->itemfeedback != null) {
                        $this->itemfeedback->addFlow_mat($flow_mat);
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
                if (strlen($this->item->getQuestionType())) {
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
                $question->fromXML($this->item, $this->qpl_id, $this->tst_id, $this->tst_object, $this->question_counter, $this->import_mapping);
                $this->numImportedItems++;
                break;
            case "material":
                if ($this->material) {
                    $mat = $this->material->getMaterial(0);
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
        }
        $this->depth[$a_xml_parser]--;
    }

    public function handlerCharacterData(XMLParser $a_xml_parser, string $a_data) : void
    {
        switch ($this->parser_mode) {
            case IL_MO_PARSE_QTI:
                $this->handlerParseCharacterData($a_xml_parser, $a_data);
                break;
            case IL_MO_VERIFY_QTI:
                $this->handlerVerifyCharacterData($a_xml_parser, $a_data);
                break;
        }
    }

    public function handlerParseCharacterData(XMLParser $a_xml_parser, string $a_data) : void
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
     * @param array<string, string> $a_attribs
     */
    public function handlerVerifyBeginTag(XMLParser $a_xml_parser, string $a_name, array $a_attribs) : void
    {
        $this->qti_element = $a_name;

        switch (strtolower($a_name)) {
            case "assessment":
                $this->assessment = $this->assessments[] = new ilQTIAssessment();
                $this->in_assessment = true;
                if (is_array($a_attribs)) {
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
                break;
            case "questestinterop":
                $this->verifyroot = true;
                break;
            case "qtimetadatafield":
                $this->metadata = array("label" => "", "entry" => "");
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
                if (is_array($a_attribs)) {
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "title":
                                $title = $value;
                                break;
                        }
                    }
                }
                $this->founditems[] = array("title" => "$title", "type" => "", "ident" => $a_attribs["ident"]);
                break;
            case "response_lid":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    // test for non ILIAS generated question types
                    if (is_array($a_attribs)) {
                        foreach ($a_attribs as $attribute => $value) {
                            switch (strtolower($attribute)) {
                                case "rcardinality":
                                    switch (strtolower($value)) {
                                        case "single":
                                            $this->founditems[count($this->founditems) - 1]["type"] = QT_MULTIPLE_CHOICE_SR;
                                            break;
                                        case "multiple":
                                            $this->founditems[count($this->founditems) - 1]["type"] = QT_MULTIPLE_CHOICE_MR;
                                            break;
                                        case "ordered":
                                            $this->founditems[count($this->founditems) - 1]["type"] = QT_ORDERING;
                                            break;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case "response_str":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    // test for non ILIAS generated question types
                    if (is_array($a_attribs)) {
                        foreach ($a_attribs as $attribute => $value) {
                            switch (strtolower($attribute)) {
                                case "rcardinality":
                                    switch (strtolower($value)) {
                                        case "single":
                                            $this->founditems[count($this->founditems) - 1]["type"] = QT_CLOZE;
                                            break;
                                        case "ordered":
                                            $this->founditems[count($this->founditems) - 1]["type"] = QT_TEXT;
                                            break;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case "response_xy":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = QT_IMAGEMAP;
                }
                break;
            case "response_num":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = QT_NUMERIC;
                }
                break;
            case "response_grp":
                if (strlen($this->founditems[count($this->founditems) - 1]["type"]) == 0) {
                    $this->founditems[count($this->founditems) - 1]["type"] = QT_MATCHING;
                }
                break;
            case "qticomment":
                // check for "old" ILIAS qti format (not well formed)
                $this->verifyqticomment = 1;
                break;
            case "presentation":
                if (is_array($a_attribs)) {
                    foreach ($a_attribs as $attribute => $value) {
                        switch (strtolower($attribute)) {
                            case "label":
                                $this->founditems[count($this->founditems) - 1]["title"] = $value;
                                break;
                        }
                    }
                }
                break;
        }
    }

    public function handlerVerifyEndTag(XMLParser $a_xml_parser, string $a_name) : void
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
                $this->metadata = array("label" => "", "entry" => "");
                break;
            case "fieldlabel":
                $this->verifyfieldlabel = 0;
                break;
            case "fieldentry":
                $this->verifyfieldentry = 0;
                break;
        }
    }

    public function handlerVerifyCharacterData(XMLParser $a_xml_parser, string $a_data) : void
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
     * @return array [["title" => string, "type" => string, "ident" => string]]
     */
    public function &getFoundItems()
    {
        return $this->founditems;
    }

    /**
     * Get array of new created questions for import id.
     * @return array<string, ['test' => mixed]>
     */
    public function getImportMapping() : array
    {
        if (!is_array($this->import_mapping)) {
            return array();
        }

        return $this->import_mapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuestionIdMapping() : array
    {
        $questionIdMapping = array();

        foreach ($this->getImportMapping() as $k => $v) {
            $oldQuestionId = substr($k, strpos($k, 'qst_') + strlen('qst_'));
            $newQuestionId = $v['test']; // yes, this is the new question id ^^

            $questionIdMapping[$oldQuestionId] = $newQuestionId;
        }

        return $questionIdMapping;
    }

    public function setXMLContent(string $a_xml_content) : void
    {
        $a_xml_content = $this->cleanInvalidXmlChars($a_xml_content);

        parent::setXMLContent($a_xml_content);
    }

    protected function openXMLFile()
    {
        $xmlContent = file_get_contents($this->xml_file);
        $xmlContent = $this->cleanInvalidXmlChars($xmlContent);
        file_put_contents($this->xml_file, $xmlContent);

        return parent::openXMLFile();
    }

    /**
     * @param string $versionDateString
     * @return string|null
     */
    protected function fetchNumericVersionFromVersionDateString($versionDateString)
    {
        $matches = null;

        if (preg_match('/^(\d+\.\d+\.\d+) .*$/', $versionDateString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $itemIdent
     * @return string|null
     */
    protected function fetchSourceNicFromItemIdent($itemIdent)
    {
        $matches = null;

        if (preg_match('/^il_(\d+?)_qst_\d+$/', $itemIdent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $xmlContent
     * @param string
     */
    protected function cleanInvalidXmlChars($xmlContent)
    {
        // http://www.w3.org/TR/xml/#charsets

        // DOES ACTUALLY KILL CONTENT, SHOULD CLEAN NON ESCAPED ILLEGAL CHARS, DON'T KNOW
        //$reg = '/[^\x09\x0A\x0D\x20-\uD7FF\uE000-\uFFFD\u10000-\u10FFFF]/';
        //$xmlContent = preg_replace($reg, '', $xmlContent);

        // remove illegal chars escaped to html entities
        $needles = array();
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

    public function getNumImportedItems() : int
    {
        return $this->numImportedItems;
    }

    protected function isMatImageAvailable() : bool
    {
        if (!$this->material) {
            return false;
        }

        if (!$this->matimage) {
            return false;
        }

        return true;
    }

    /**
     * @param string $buffer (any data, binary)
     */
    protected function virusDetected($buffer) : bool
    {
        $vs = ilVirusScannerFactory::_getInstance();

        if ($vs === null) {
            return false; // no virus scan, no virus detected
        }

        return (bool) $vs->scanBuffer($buffer);
    }
}
