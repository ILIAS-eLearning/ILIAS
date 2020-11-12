<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilCronJobRepository
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilCronJobRepository
{
    /**
     * @return ilCronJobCollection
     */
    public function findAll() : ilCronJobCollection;
}
