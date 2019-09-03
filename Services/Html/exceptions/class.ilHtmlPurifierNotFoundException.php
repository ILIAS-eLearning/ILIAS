<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class for html related exception handling in ILIAS.
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierNotFoundException extends ilHtmlException
{
    /**
     * ilHtmlPurifierNotFoundException constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}