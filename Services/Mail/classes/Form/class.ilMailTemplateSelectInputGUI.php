<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Services/JSON/classes/class.ilJsonUtil.php';

/**
 * Class ilMailTemplateSelectInputGUI
 */
class ilMailTemplateSelectInputGUI extends ilSelectInputGUI
{
    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $a_title
     * @param string $a_postvar
     * @param string $url
     * @param array  $fields
     */
    public function __construct($a_title = '', $a_postvar = '', $url = '', array $fields = array())
    {
        parent::__construct($a_title, $a_postvar);

        $this->url = $url;
        $this->fields = $fields;
    }

    /**
     * @param string $a_mode
     * @return string
     */
    public function render($a_mode = '')
    {
        $html = parent::render($a_mode);

        $tpl = new ilTemplate('tpl.prop_template_select_container.html', true, true, 'Services/Mail');
        $tpl->setVariable('CONTENT', $html);
        $tpl->setVariable('FIELDS', ilJsonUtil::encode($this->fields));
        $tpl->setVariable('URL', $this->url);
        $tpl->setVariable('ID', $this->getFieldId());

        return $tpl->get();
    }
}
