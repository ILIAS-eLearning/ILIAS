<?php

declare(strict_types=1);

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
 * Class ilObjTermsOfService
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjTermsOfService extends ilObject2
{
    private bool $reevaluateOnLogin;
    private bool $status;
    protected ilSetting $settings;

    /**
     * @param int  $a_id
     * @param bool $a_reference
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->settings = $DIC['ilSetting'];
        $this->doRead();
    }

    protected function initType(): void
    {
        $this->type = 'tos';
    }


    protected function doRead() : void
    {
        $this->status = (bool) $this->settings->get('tos_status', '0');
        $this->reevaluateOnLogin = (bool) $this->settings->get('tos_reevaluate_on_login', '0');
    }

    public function resetAll(): void
    {
        $in = $this->db->in('usr_id', [ANONYMOUS_USER_ID, SYSTEM_USER_ID], true, 'integer');
        $this->db->manipulate("UPDATE usr_data SET agree_date = NULL WHERE $in");

        $this->settings->set('tos_last_reset', (string) time());
    }

    public function getLastResetDate(): ilDateTime
    {
        return new ilDateTime((int) $this->settings->get('tos_last_reset', '0'), IL_CAL_UNIX);
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setReevaluateOnLogin(bool $status): void
    {
        $this->reevaluateOnLogin = $status;
    }

    public function shouldReevaluateOnLogin(): bool
    {
        return $this->reevaluateOnLogin;
    }

    public function bindFormInput(array $values) : void
    {
        $status = (bool) ($values[ilObjTermsOfServiceGUI::F_TOS_STATUS] ?? false);
        $reevaluate_on_login = (bool) ($values[ilObjTermsOfServiceGUI::F_TOS_STATUS][ilObjTermsOfServiceGUI::F_TOS_REEVALUATE_ON_LOGIN] ?? $this->shouldReevaluateOnLogin());
        if (!$status) {
            $this->setStatus($status);
        } else {
            $this->setStatus($status);
            $this->setReevaluateOnLogin($reevaluate_on_login);
        }
    }

    public function store() : void
    {
        $this->settings->set('tos_status', (string) ((int) $this->getStatus()));
        $this->settings->set('tos_reevaluate_on_login', (string) ((int) $this->shouldReevaluateOnLogin()));
    }
}
