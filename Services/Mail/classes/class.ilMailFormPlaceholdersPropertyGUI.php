<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
*
* @ingroup ServicesMail
*/
include_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

class ilMailFormPlaceholdersPropertyGUI extends ilFormPropertyGUI
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * ilMailFormPlaceholdersPropertyGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();

        parent::__construct('');
    }

    /**
     * @param $a_tpl
     */
    public function insert($a_tpl)
    {
        $subtpl = new ilTemplate("tpl.mail_new_placeholders.html", false, false, "Services/Mail");
        $subtpl->setVariable('TXT_USE_PLACEHOLDERS', $this->lng->txt('mail_nacc_use_placeholder'));
        $subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        $subtpl->setVariable('TXT_MAIL_SALUTATION', $this->lng->txt('mail_nacc_salutation'));
        $subtpl->setVariable('TXT_FIRST_NAME', $this->lng->txt('firstname'));
        $subtpl->setVariable('TXT_LAST_NAME', $this->lng->txt('lastname'));
        $subtpl->setVariable('TXT_LOGIN', $this->lng->txt('mail_nacc_login'));
        $subtpl->setVariable('TXT_ILIAS_URL', $this->lng->txt('mail_nacc_ilias_url'));
        $subtpl->setVariable('TXT_CLIENT_NAME', $this->lng->txt('mail_nacc_client_name'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $subtpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
