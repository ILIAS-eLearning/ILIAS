<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailBody
{
    /**
     * @var mixed|null|string|string[]
     */
    private $bodyContent;

    /**
     * @param string $content
     * @param ilMailBodyPurifier $purifier
     */
    public function __construct(string $content, ilMailBodyPurifier $purifier)
    {
        $this->bodyContent = $purifier->purify($content);
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getContent() : string
    {
        return $this->bodyContent;
    }
}
