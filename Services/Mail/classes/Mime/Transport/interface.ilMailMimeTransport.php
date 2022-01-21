<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailMimeTransport
 */
interface ilMailMimeTransport
{
    public function send(ilMimeMail $mail) : bool;
}
