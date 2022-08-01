<?php
declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 ********************************************************************
 */

namespace ILIAS\Services\Mail\AutoResponder;

interface ilAutoResponderRepository
{
    public function findBySenderId(int $sender_id) : array;
    public function findByReceiverId(int $receiver_id) : array;
    public function findBySenderIdAndReceiverId(int $sender_id, int $receiver_id) : ?ilAutoResponder;
    public function read(ilAutoResponder $auto_responder) : ilAutoResponder;
    public function store(ilAutoResponder $auto_responder) : void;
    public function delete(ilAutoResponder $auto_responder) : void;
    public function deleteBySenderId(int $sender_id) : void;
}