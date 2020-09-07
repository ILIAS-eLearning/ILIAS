<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceJsonSerialization
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceJsonSerialization
{
    public function toJson() : string;
}
