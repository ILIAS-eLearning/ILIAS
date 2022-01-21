<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTransportNull
 */
class ilMailMimeTransportNull implements ilMailMimeTransport
{
    public function send(ilMimeMail $mail) : bool
    {
        ilLoggerFactory::getLogger('mail')->debug(
            'Suppressed delegation of external email delivery according to global setting.'
        );

        return true;
    }
}
