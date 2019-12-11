<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSignableDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceSignableDocument
{
    /**
     * @return string
     */
    public function content() : string;

    /**
     * @return string
     */
    public function title() : string;

    /**
     * @return int
     */
    public function id() : int;

    /**
     * @return \ilTermsOfServiceEvaluableCriterion[]
     */
    public function criteria() : array;
}
