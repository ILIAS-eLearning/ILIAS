<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilSamlIdpSelectionTableGUI
 */
class ilSamlIdpSelectionTableGUI extends \ilTable2GUI
{
    /**
     * @inheritdoc
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
    {
        $this->setId('saml_idp_selection');
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->disable('sort');
        $this->disable('header');
        $this->disable('linkbar');
        $this->disable('numinfo');
        $this->setLimit(PHP_INT_MAX);
        $this->setShowRowsSelector(false);
        
        $this->setTitle($this->lng->txt('auth_saml_idp_selection_table_title'));
        $this->setDescription($this->lng->txt('auth_saml_idp_selection_table_desc'));

        $this->setRowTemplate('tpl.saml_idp_selection_row.html', 'Services/Saml');
    }
}
