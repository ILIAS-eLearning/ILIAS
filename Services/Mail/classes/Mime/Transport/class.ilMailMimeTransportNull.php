<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTransportNull
 */
class ilMailMimeTransportNull implements \ilMailMimeTransport
{
    /**
     * @inheritdoc
     */
    public function send(\ilMimeMail $mail) : bool
    {
        ilLoggerFactory::getLogger('mail')->debug(sprintf(
            'Suppressed delegation of external email delivery according to global setting.'
        ));

        return true;
    }
}
