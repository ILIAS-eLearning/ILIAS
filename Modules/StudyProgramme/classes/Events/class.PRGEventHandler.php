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

class PRGEventHandler
{
    protected ilPRGMail $mail;
    protected array $certificate_lock = [];
    protected array $lp_lock = [];

    public function __construct(
        ilPRGMail $mail
    ) {
        $this->mail = $mail;
        $this->certificate_lock = [];
        $this->lp_lock = [];
    }

    public function updateLPStatus(int $prg_obj_id, int $usr_id): void
    {
        $k = $prg_obj_id . '-' . $usr_id;
        if (! in_array($k, $this->lp_lock)) {
            ilLPStatusWrapper::_updateStatus($prg_obj_id, $usr_id);
            $this->lp_lock[] = $k;
        }
    }

    public function triggerCertificateOnce(\Closure $cert, int $prg_obj_id, int $usr_id): void
    {
        $k = $prg_obj_id . '-' . $usr_id;
        if (! in_array($k, $this->certificate_lock)) {
            $cert();
            $this->certificate_lock[] = $k;
        }
    }

    public function sendRiskyToFailMail(int $assignment_id, int $root_prg_id): void
    {
        $this->mail->sendRiskyToFailMail($assignment_id, $root_prg_id);
    }

    public function sendInformToReAssignMail(int $assignment_id, int $root_prg_id): void
    {
        $this->mail->sendInformToReAssignMail($assignment_id, $root_prg_id);
    }

    public function sendReAssignedMail(int $assignment_id, int $root_prg_id): void
    {
        $this->mail->sendReAssignedMail($assignment_id, $root_prg_id);
    }

    public function resetMailFlagValidity(int $assignment_id, int $root_prg_id): void
    {
        $this->mail->resetExpiryInfoSentFor($assignment_id, $root_prg_id);
    }
    public function resetMailFlagDeadline(int $assignment_id, int $root_prg_id): void
    {
        $this->mail->resetRiskyToFailSentFor($assignment_id, $root_prg_id);
    }
}
