<?php declare(strict_types=1);

/**
 * Class ilAsyncContainerSelectionExplorer
 * A class for async ilContainerSelectionExplorer which triggers
 * "async_explorer-add_reference" event on the body when clicking a node
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncContainerSelectionExplorer extends ilContainerSelectionExplorer
{
    /**
     * @var array additional js config
     */
    protected array $js_conf;

    /**
     * @var array stored js onload codes
     */
    protected static array $js_on_load_added = array();

    /**
     * @param $target string url for the onclick event of a node
     */
    public function __construct(string $target)
    {
        parent::__construct($target);

        $this->addJsConf('save_explorer_url', $target);
    }

    /**
     * Adds the javascript to template
     */
    public static function addJavascript() : void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");
    }

    /**
     * Creates the onclick function call
     */
    public function buildOnClick($node_id, string $type, string $title) : string
    {
        $result = "";
        $ref_id = (int) $_GET['ref_id'];
        if ($ref_id) {
            $result =
                "$('body').trigger('async_explorer-add_reference', {target_id: '" .
                $node_id .
                "', type: '" .
                $type .
                "', parent_id: '" .
                $ref_id .
                "'});";
        }
        return $result;
    }

    /**
     * Sets the href-value to a void js call
     */
    public function buildLinkTarget($node_id, string $type) : string
    {
        return "javascript:void(0);";
    }

    /**
     * Returns the explorer html and adds the javascript to the template
     */
    public function getOutput() : string
    {
        self::initJs();

        return parent::getOutput();
    }

    /*
     * Initializes the js
     * Adds the on load code for the async explorer
     */
    public function initJs() : void
    {
        self::addOnLoadCode(
            'explorer',
            '$("#' . $this->getId() . '").study_programme_async_explorer(' . json_encode($this->js_conf) . ');'
        );
    }

    /**
     * Adds onload code to the template
     */
    protected function addOnLoadCode(string $id, string $content) : void
    {
        if (!isset(self::$js_on_load_added[$id])) {
            $this->tpl->addOnLoadCode($content);
            self::$js_on_load_added[$id] = $content;
        }
    }

    /**
     * Adds additional js to the onload code of the async explorer
     */
    public function addJsConf(string $key, string $value) : void
    {
        $this->js_conf[$key] = $value;
    }

    /**
     * Returns a certain setting of the additional configuration
     */
    public function getJsConf(string $key) : string
    {
        return $this->js_conf[$key];
    }
}
