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
 *********************************************************************/

use ILIAS\Refinery\Factory;

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
     * @var array<int, string> stored js onload codes
     */
    protected static array $js_on_load_added = [];
    
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;

    /**
     * @param $target string url for the onclick event of a node
     */
    public function __construct(string $target, Factory $refinery, ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper)
    {
        parent::__construct($target);

        $this->addJsConf('save_explorer_url', $target);

        $this->request_wrapper = $request_wrapper;
        $this->refinery = $refinery;
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
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
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
        $this->initJs();

        return parent::getOutput();
    }

    /*
     * Initializes the js
     * Adds the on load code for the async explorer
     */
    public function initJs() : void
    {
        $this->addOnLoadCode(
            'explorer',
            '$("#' . $this->getId() . '").study_programme_async_explorer(' . json_encode(
                $this->js_conf,
                JSON_THROW_ON_ERROR
            ) . ');'
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
