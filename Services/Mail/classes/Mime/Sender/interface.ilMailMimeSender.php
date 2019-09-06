<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailMimeTransport
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailMimeSender
{
    /**
     * @return bool
     */
    public function hasReplyToAddress() : bool;

    /**
     * @return string
     */
    public function getReplyToAddress() : string;

    /**
     * @return string
     */
    public function getReplyToName() : string;

    /**
     * @return bool
     */
    public function hasEnvelopFromAddress() : bool;

    /**
     * @return string
     */
    public function getEnvelopFromAddress() : string;

    /**
     * @return string
     */
    public function getFromAddress() : string;

    /**
     * @return string
     */
    public function getFromName() : string;
}