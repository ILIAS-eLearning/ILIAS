<?php

/**
 * Class ilOrgUnitAuthorityInputGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAuthorityInputGUI extends ilFormPropertyGUI implements ilMultiValuesItem
{

    /**
     * @var ilOrgUnitAuthority[]
     */
    protected $value;


    /**
     * ilOrgUnitAuthorityInputGUI constructor.
     *
     * @param string $a_title
     * @param string $a_postvar
     */
    public function __construct($a_title, $a_postvar)
    {
        parent::__construct($a_title, $a_postvar);
        ilOrgUnitAuthority::replaceNameRenderer(function ($id) {
            /**
             * @var $a ilOrgUnitAuthority
             */
            $a = ilOrgUnitAuthority::find($id);
            $data = array( 'id' => $id, 'over' => $a->getOver(), 'scope' => $a->getScope() );

            return json_encode($data);
        });
    }


    /**
     * @param \ilTemplate $a_tpl
     */
    public function insert(ilTemplate $a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }


    /**
     * @param array $values
     */
    public function setValueByArray(array $values)
    {
        $authorities = $values[$this->getPostVar()];
        if (!is_array($authorities)) {
            $authorities = [];
        }
        foreach ($authorities as $authority) {
            assert($authority instanceof ilOrgUnitAuthority);
        }
        $this->setValue($authorities);
    }


    /**
     * @param $a_value \ilOrgUnitAuthority[]
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }


    /**
     * @return \ilOrgUnitAuthority[]
     */
    public function getValue()
    {
        return $this->value;
    }


    protected function render()
    {
        $tpl = new ilTemplate("tpl.authority_input.html", true, true, "Modules/OrgUnit");
        //		if (strlen($this->getValue())) {
        //			$tpl->setCurrentBlock("prop_text_propval");
        //			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        //			$tpl->parseCurrentBlock();
        //		}

        //$tpl->setVariable("POSITION_ID", $this->getFieldId());

        $postvar = $this->getPostVar();
        //		if ($this->getMulti() && substr($postvar, - 2) != "[]") {
        //			$postvar .= "[]";
        //		}

        $tpl->setVariable("POST_VAR", $postvar);

        // SCOPE
        $scope_html = "";
        foreach (ilOrgUnitAuthority::getScopes() as $scope) {
            $txt = $this->dic()->language()->txt('scope_' . $scope);
            $scope_html .= "<option value='{$scope}'>{$txt}</option>";
        }
        $tpl->setVariable("SCOPE_OPTIONS", $scope_html);

        // Over
        $over_everyone = ilOrgUnitAuthority::OVER_EVERYONE;
        $title = $this->lang()->txt('over_' . $over_everyone);
        $over_html = "<option value='{$over_everyone}'>{$title}</option>";
        foreach (ilOrgUnitPosition::getArray('id', 'title') as $id => $title) {
            $over_html .= "<option value='{$id}'>{$title}</option>";
        }
        $tpl->setVariable("OVER_OPTIONS", $over_html);
        /**
         * @var $ilOrgUnitAuthority ilOrgUnitAuthority
         */
        if ($this->getMultiValues()) {
            foreach ($this->getMultiValues() as $ilOrgUnitAuthority) {
                //				$tpl->setVariable("OVER_OPTIONS", $over_html);
            }
        }

        if ($this->getRequired()) {
            //			$tpl->setVariable("REQUIRED", "required=\"required\"");
        }

        $tpl->touchBlock("inline_in_bl");
        $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        $this->initJS();

        return $tpl->get();
    }


    /**
     * @return \ILIAS\DI\Container
     */
    protected function dic()
    {
        return $GLOBALS["DIC"];
    }


    /**
     * @return \ilLanguage
     */
    protected function lang()
    {
        static $loaded;
        $lang = $this->dic()->language();
        if (!$loaded) {
            $lang->loadLanguageModule('orgu');
            $loaded = true;
        }

        return $lang;
    }


    /**
     * @return bool
     */
    public function getMulti()
    {
        return false;
    }


    protected function initJS()
    {
        // Global JS
        /**
         * @var $globalTpl \ilTemplate
         */
        $globalTpl = $GLOBALS['DIC'] ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
        $globalTpl->addJavascript("./Modules/OrgUnit/templates/default/authority.js");
        $config = json_encode(array());
        $data = json_encode($this->getValue());
        $globalTpl->addOnLoadCode("ilOrgUnitAuthorityInput.init({$config}, {$data});");
    }
}
