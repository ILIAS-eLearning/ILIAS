<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLetterAvatar
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilUserAvatar
{
    public function getUrl() : string;

    public function setUsrId(int $usrId) : void;

    public function setName(string $name) : void;
}
