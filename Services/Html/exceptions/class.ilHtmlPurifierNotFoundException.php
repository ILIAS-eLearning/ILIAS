<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilHtmlPurifierNotFoundException
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierNotFoundException extends ilHtmlException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
