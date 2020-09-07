<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilAssSelfAssessmentMigrator
{
    /**
     * @param string $content
     * @return string
     */
    public function migrateToLmContent($content);
}
