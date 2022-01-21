<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailBody
{
    private string $bodyContent;

    public function __construct(string $content, ilMailBodyPurifier $purifier)
    {
        $this->bodyContent = $purifier->purify($content);
    }

    public function getContent() : string
    {
        return $this->bodyContent;
    }
}
