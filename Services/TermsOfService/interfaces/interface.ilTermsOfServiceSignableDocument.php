<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSignableDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceSignableDocument
{
    public function content() : string;

    public function title() : string;

    public function id() : int;

    /**
     * @return ilTermsOfServiceEvaluableCriterion[]
     */
    public function criteria() : array;
}
