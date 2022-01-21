<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailMimeTransport
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailMimeSender
{
    public function hasReplyToAddress() : bool;

    public function getReplyToAddress() : string;

    public function getReplyToName() : string;

    public function hasEnvelopFromAddress() : bool;

    public function getEnvelopFromAddress() : string;

    public function getFromAddress() : string;

    public function getFromName() : string;
}
