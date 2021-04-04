<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

/**
 * Command action handler interface
 * @author Alexander Killing <killing@leifos.de>
 */
interface CommandActionHandler
{
    /**
     * @param $query
     * @param $body
     * @return Response
     */
    public function handle($query, $body) : Response;
}
