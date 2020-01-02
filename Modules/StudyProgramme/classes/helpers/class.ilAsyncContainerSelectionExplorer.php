<?php
require_once("./Services/ContainerReference/classes/class.ilContainerSelectionExplorer.php");

/**
 * Class ilAsyncContainerSelectionExplorer
 * A class for a async ilContainerSelectionExplorer which triggers a "async_explorer-add_reference" event on the body when clicking a node
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncContainerSelectionExplorer extends ilContainerSelectionExplorer
{

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var array additional js config
     */
    protected static $js_conf;

    /**
     * @var array stored js onload codes
     */
    protected static $js_on_load_added = array();


    /**
     * @param $a_target url for the onclick event of a node
     */
    public function __construct($a_target)
    {
        parent::__construct($a_target);

        global $DIC;
        $tpl = $DIC['tpl'];
        $this->tpl = $tpl;

        $this->addJsConf('save_explorer_url', $a_target);
    }


    /**
     * Adds the javascript to template
     */
    public static function addJavascript()
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");
    }


    /**
     * Creates the onclick function call
     *
     * @param $a_node_id
     * @param $a_type
     * @param $a_title
     *
     * @return string
     */
    public function buildOnClick($a_node_id, $a_type, $a_title)
    {
        $ref_id = (int) $_GET['ref_id'];
        if ($ref_id) {
            return "$('body').trigger('async_explorer-add_reference', {target_id: '" . $a_node_id . "', type: '" . $a_type . "', parent_id: '" . $ref_id . "'});";
        }
    }


    /**
     * Sets the href-value to a void js call
     *
     * @param $a_node_id
     * @param $a_type
     *
     * @return string
     */
    public function buildLinkTarget($a_node_id, $a_type)
    {
        return "javascript:void(0);";
    }


    /**
     * Returns the explorer html and adds the javascripts to the template
     *
     * @return string
     */
    public function getOutput()
    {
        self::initJs();

        return parent::getOutput();
    }

    /*
     * Initializes the js
     * Adds the on load code for the async explorer
     */
    public function initJs()
    {
        self::addOnLoadCode('explorer', '$("#' . $this->getId() . '").study_programme_async_explorer(' . json_encode($this->js_conf) . ');');
    }

    /**
     * Adds onload code to the template
     *
     * @param $id
     * @param $content
     */
    protected function addOnLoadCode($id, $content)
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        if (!isset(self::$js_on_load_added[$id])) {
            $tpl->addOnLoadCode($content);
            self::$js_on_load_added[$id] = $content;
        }
    }

    /**
     * Adds additional js to the onload code of the async explorer
     *
     * @param array $js_conf
     */
    public function addJsConf($key, $value)
    {
        $this->js_conf[$key] = $value;
    }

    /**
     * Returns a certain setting of the additional configuration
     *
     * @return string
     */
    public function getJsConf($key)
    {
        return $this->js_conf[$key];
    }
}
