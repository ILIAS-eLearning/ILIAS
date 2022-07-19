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

/**
*
* @author Jan Posselt <jposselt@databay.de>
* @ingroup ServicesMail
*/
class ilMailFormPlaceholdersPropertyGUI extends ilFormPropertyGUI
{
    public function insert(ilTemplate $a_tpl) : void
    {
        $subtpl = new ilTemplate(
            'tpl.mail_new_placeholders.html',
            false,
            false,
            'Services/Mail'
        );
        $subtpl->setVariable('TXT_USE_PLACEHOLDERS', $this->lng->txt('mail_nacc_use_placeholder'));
        $subtpl->setVariable(
            'TXT_PLACEHOLDERS_ADVISE',
            sprintf($this->lng->txt('placeholders_advise'), '<br />')
        );
        $subtpl->setVariable('TXT_MAIL_SALUTATION', $this->lng->txt('mail_nacc_salutation'));
        $subtpl->setVariable('TXT_FIRST_NAME', $this->lng->txt('firstname'));
        $subtpl->setVariable('TXT_LAST_NAME', $this->lng->txt('lastname'));
        $subtpl->setVariable('TXT_LOGIN', $this->lng->txt('mail_nacc_login'));
        $subtpl->setVariable('TXT_ILIAS_URL', $this->lng->txt('mail_nacc_ilias_url'));
        $subtpl->setVariable('TXT_INSTALLATION_NAME', $this->lng->txt('mail_nacc_installation_name'));

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $subtpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
