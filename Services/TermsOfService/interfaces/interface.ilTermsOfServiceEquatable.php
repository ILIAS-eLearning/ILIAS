<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceEquatable
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceEquatable
{
    /**
     * @param mixed $other
     * @return bool
     */
    public function equals($other) : bool;
}
