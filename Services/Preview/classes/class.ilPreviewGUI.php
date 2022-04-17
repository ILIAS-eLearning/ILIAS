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
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * User interface class for previewing objects.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesPreview
 */
class ilPreviewGUI implements ilCtrlBaseClassInterface
{
    private ?int $node_id = null;
    private ?int $obj_id = null;
    private ?\ilPreview $preview = null;
    /**
     * @var ilWorkspaceAccessHandler|ilAccessHandler|null
     */
    private ?object $access_handler = null;
    private ?int $context = null;
    private ?\ilCtrl $ctrl = null;
    private ?\ilLanguage $lng = null;
    private static bool $initialized = false;

    public const CONTEXT_REPOSITORY = 1;
    public const CONTEXT_WORKSPACE = 2;

    /**
     * Creates a new preview GUI.
     * @param int $a_node_id The node id.
     * @param int $a_context The context of the preview.
     * @param int $a_obj_id The object id.
     * @param ilWorkspaceAccessHandler|ilAccessHandler|null $a_access_handler The access handler to use.
     */
    public function __construct(
        ?int $a_node_id = null,
        ?int $a_context = self::CONTEXT_REPOSITORY,
        ?int $a_obj_id = null,
        ?object $a_access_handler = null
    ) {
        global $DIC;
        // assign values
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilAccess = $DIC['ilAccess'];

        $query = $DIC->http()->wrapper()->query();
        $base_class = $query->has('baseClass')
            ? $query->retrieve('baseClass', $DIC->refinery()->to()->string())
            : null;

        // if we are the base class, get the id's from the query string
        if (strtolower($base_class) === strtolower(__CLASS__)) {
            $this->node_id = $query->has('node_id')
                ? $query->retrieve('node_id', $DIC->refinery()->kindlyTo()->int())
                : 0;
            $this->context = $query->has('context')
                ? $query->retrieve('context', $DIC->refinery()->kindlyTo()->int())
                : self::CONTEXT_REPOSITORY;
            $a_obj_id = $query->has('obj_id')
                ? $query->retrieve('obj_id', $DIC->refinery()->kindlyTo()->int())
                : null;
        } else {
            $this->node_id = $a_node_id;
            $this->context = $a_context;
        }



        // access handler NOT provided?
        if ($a_access_handler === null) {
            if ($this->context === self::CONTEXT_WORKSPACE) {
                $a_access_handler = new ilWorkspaceAccessHandler();
            } else {
                $a_access_handler = $ilAccess;
            }
        }
        $this->access_handler = $a_access_handler;

        // object id NOT provided?
        if ($a_obj_id === null) {
            if ($this->context === self::CONTEXT_WORKSPACE) {
                $a_obj_id = $this->access_handler->getTree()->lookupObjectId($this->node_id);
            } else {
                $a_obj_id = ilObject::_lookupObjId($this->node_id);
            }
        }
        $this->obj_id = $a_obj_id;

        // create preview object
        $this->preview = new ilPreview($this->obj_id);

        // if the call is NOT async initialize our stuff
        if (!$this->ctrl->isAsynch()) {
            self::initPreview();
        }
    }


    /**
    * execute command
    */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("getPreviewHTML");
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                return $this->$cmd();
                break;
        }
    }

    /**
     * Gets the JavaScript code to show the preview.
     * @param $a_html_id string The id of the HTML element that contains the preview.
     * @return string The JavaScript code to show the preview.
     */
    public function getJSCall(string $a_html_id) : string
    {
        $status = $this->preview->getRenderStatus();
        $command = $status === ilPreview::RENDER_STATUS_NONE ? "renderPreview" : "";
        $loading_text = self::jsonSafeString(
            $this->lng->txt($status === ilPreview::RENDER_STATUS_NONE ? "preview_status_creating" : "preview_loading")
        );

        // build the url
        $link = $this->buildUrl($command);
        return "il.Preview.toggle(event, { id: '{$this->node_id}', htmlId: '{$a_html_id}', url: '$link', status: '$status', loadingText: '$loading_text' });";
    }

    /**
     * Gets the HTML that displays the preview.
     * @return string The HTML that displays the preview.
     */
    public function getPreviewHTML() : string
    {
        // load the template
        $tmpl = new ilTemplate("tpl.preview.html", true, true, "Services/Preview");
        $tmpl->setVariable("PREVIEW_ID", $this->getHtmlId());

        // check for read access and get object id
        $preview_status = $this->preview->getRenderStatus();

        // has read access?
        if ($this->access_handler->checkAccess("read", "", $this->node_id)) {
            // preview images available?
            $images = $this->preview->getImages();
            if (count($images) > 0) {
                foreach ($images as $image) {
                    $tmpl->setCurrentBlock("preview_item");
                    $tmpl->setVariable("IMG_URL", ilWACSignedPath::signFile($image["url"]));
                    $tmpl->setVariable("WIDTH", $image["width"]);
                    $tmpl->setVariable("HEIGHT", $image["height"]);
                    $tmpl->parseCurrentBlock();
                }
            } else {
                // set text depending on the status
                $tmpl->setCurrentBlock("no_preview");
                switch ($preview_status) {
                    case ilPreview::RENDER_STATUS_PENDING:
                        $tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_pending"));
                        break;

                    case ilPreview::RENDER_STATUS_FAILED:
                        $tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_failed"));
                        break;

                    default:
                        $tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("preview_status_missing"));
                        break;
                }
                $tmpl->parseCurrentBlock();
            }
        } else {
            // display error message
            $tmpl->setVariable("TXT_NO_PREVIEW", $this->lng->txt("no_access_item"));
        }

        // output
        if ($this->ctrl->isAsynch()) {
            $response = new stdClass();
            $response->html = $tmpl->get();
            $response->status = $preview_status;

            // send response object (don't use 'application/json' as IE wants to download it!)
            header('Vary: Accept');
            header('Content-type: text/plain');
            echo json_encode($response, JSON_THROW_ON_ERROR);

            // no further processing!
            exit;
        }

        return $tmpl->get();
    }

    /**
     * Gets the HTML that is used for displaying the preview inline.
     * @return string The HTML that is used for displaying the preview inline.
     */
    public function getInlineHTML() : string
    {
        $tmpl = new ilTemplate("tpl.preview_inline.html", true, true, "Services/Preview");
        $tmpl->setVariable("PREVIEW", $this->getPreviewHTML());

        // rendering allowed?
        if ($this->access_handler->checkAccess("read", "", $this->node_id)) {
            $this->renderCommand(
                $tmpl,
                "render",
                "preview_create",
                "preview_status_creating",
                array(ilPreview::RENDER_STATUS_NONE, ilPreview::RENDER_STATUS_FAILED)
            );
        }

        // delete allowed?
        if ($this->access_handler->checkAccess("write", "", $this->node_id)) {
            $this->renderCommand(
                $tmpl,
                "delete",
                "preview_delete",
                "preview_status_deleting",
                array(ilPreview::RENDER_STATUS_CREATED)
            );
        }

        return $tmpl->get();
    }

    /**
     * Renders a command to the specified template.
     * @param $tmpl object The template.
     * @param $a_cmd string The command to create.
     * @param $btn_topic string The topic to get the button text.
     * @param $loading_topic string The topic to get the loading text.
     * @param $a_display_status string[] An array containing the statuses when the command should be visible.
     */
    private function renderCommand(ilTemplate $tmpl, string $a_cmd, string $btn_topic, string $loading_topic, array $a_display_status) : void
    {
        $preview_html_id = $this->getHtmlId();
        $preview_status = $this->preview->getRenderStatus();
        $loading_text = self::jsonSafeString($this->lng->txt($loading_topic));

        $link = $this->buildUrl($a_cmd . "Preview");
        $script_args = "event, { id: '{$this->node_id}', htmlId: '$preview_html_id', url: '$link', loadingText: '$loading_text' }";

        $action_class = "";
        if (!in_array($preview_status, $a_display_status, true)) {
            $action_class = "ilPreviewActionHidden";
        }

        $tmpl->setCurrentBlock("preview_action");
        $tmpl->setVariable("CLICK_ACTION", "il.Preview.$a_cmd($script_args);");
        $tmpl->setVariable("ACTION_CLASS", $action_class);
        $tmpl->setVariable("ACTION_ID", "preview_{$a_cmd}_" . $preview_html_id);
        $tmpl->setVariable("TXT_ACTION", $this->lng->txt($btn_topic));
        $tmpl->parseCurrentBlock();
    }

    /**
     * Renders the preview and returns the HTML code that displays the preview.
     * @return string The HTML code that displays the preview.
     */
    public function renderPreview() : string
    {
        // has read access?
        if ($this->access_handler->checkAccess("read", "", $this->node_id)) {
            // get the object
            $obj = ilObjectFactory::getInstanceByObjId($this->obj_id);
            $this->preview->create($obj);
        }

        return $this->getPreviewHTML();
    }

    /**
     * Deletes the preview and returns the HTML code that displays the preview.
     * @return string The HTML code that displays the preview.
     */
    public function deletePreview() : string
    {
        // has read access?
        if ($this->access_handler->checkAccess("write", "", $this->node_id)) {
            $this->preview->delete();
        }

        return $this->getPreviewHTML();
    }

    /**
     * Gets the HTML id for the preview.
     * @return string The HTML id to use for the preview.
     */
    private function getHtmlId() : string
    {
        return "preview_" . $this->node_id;
    }

    /**
     * Builds the URL to call the preview GUI.
     * @param $a_cmd string The command to call.
     * @param $a_async bool true, to create a URL to call asynchronous; otherwise, false.
     * @return string The created URL.
     */
    private function buildUrl(string $a_cmd = "", bool $a_async = true) : string
    {
        $link = "ilias.php?baseClass=ilPreviewGUI&node_id={$this->node_id}&context={$this->context}&obj_id={$this->obj_id}";

        if ($a_async) {
            $link .= "&cmdMode=asynch";
        }

        if (!empty($a_cmd)) {
            $link .= "&cmd=$a_cmd";
        }

        return $link;
    }


    /**
     * Initializes the preview and loads the needed javascripts and styles.
     */
    public static function initPreview() : void
    {
        if (self::$initialized) {
            return;
        }

        global $DIC;
        // jquery
        iljQueryUtil::initjQuery();

        // load qtip
        ilTooltipGUI::init();

        // needed scripts & styles
        $DIC->ui()->mainTemplate()->addJavaScript("./libs/bower/bower_components/jquery-mousewheel/jquery.mousewheel.js");
        $DIC->ui()->mainTemplate()->addJavaScript("./Services/Preview/js/ilPreview.js");

        // create loading template
        $tmpl = new ilTemplate("tpl.preview.html", true, true, "Services/Preview");
        $tmpl->setCurrentBlock("no_preview");
        $tmpl->setVariable("TXT_NO_PREVIEW", "%%0%%");
        $tmpl->parseCurrentBlock();

        $initialHtml = str_replace(array("\r\n", "\r"), "\n", $tmpl->get());
        $lines = explode("\n", $initialHtml);
        $new_lines = array();
        foreach ($lines as $i => $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }
        $initialHtml = implode($new_lines);

        // add default texts and values
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.texts.preview = \"" . self::jsonSafeString($DIC->language()->txt("preview")) . "\";");
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.texts.showPreview = \"" . self::jsonSafeString($DIC->language()->txt("preview_show"))
                                                  . "\";");
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.texts.close = \"" . ilLegacyFormElementsUtil::prepareFormOutput(
            $DIC->language()->txt("close")
        ) . "\";");
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.previewSize = " . ilPreviewSettings::getImageSize() . ";");
        $DIC->ui()->mainTemplate()->addOnLoadCode(
            "il.Preview.initialHtml = " . json_encode($initialHtml, JSON_THROW_ON_ERROR) . ";"
        );
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.highlightClass = \"ilContainerListItemOuterHighlight\";");
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Preview.init();");

        self::$initialized = true;
    }

    /**
     * Makes the specified string safe for JSON.
     *
     * @param string $text The text to make JSON safe.
     * @return string The JSON safe text.
     */
    private static function jsonSafeString(string $text) : string
    {
        if (!is_string($text)) {
            return $text;
        }

        $text = htmlentities($text, ENT_COMPAT | ENT_HTML401, "UTF-8");
        $text = str_replace("'", "&#039;", $text);
        return $text;
    }
}
