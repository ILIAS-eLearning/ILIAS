<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceJsonDeserialization
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceJsonDeserialization
{
    /**
     * @param string $json
     */
    public function fromJson(string $json) : void;
}