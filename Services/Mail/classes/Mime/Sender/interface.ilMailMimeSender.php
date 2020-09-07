<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailMimeTransport
 */
interface ilMailMimeSender
{
    /**
     * @return bool
     */
    public function hasReplyToAddress();

    /**
     * @return string
     */
    public function getReplyToAddress();

    /**
     * @return string
     */
    public function getReplyToName();

    /**
     * @return bool
     */
    public function hasEnvelopFromAddress();

    /**
     * @return string
     */
    public function getEnvelopFromAddress();

    /**
     * @return string
     */
    public function getFromAddress();

    /**
     * @return string
     */
    public function getFromName();
}
