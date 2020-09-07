<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMarkSchemaAware
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
interface ilMarkSchemaAware
{
    /**
     * @return ASS_MarkSchema
     */
    public function getMarkSchema();

    /**
     * @return boolean|string True or an error string which can be used for display purposes
     */
    public function checkMarks();

    /**
     * @return boolean
     */
    public function canEditMarks();

    /**
     * @return int
     */
    public function getMarkSchemaForeignId();

    /**
     *
     */
    public function onMarkSchemaSaved();
}
