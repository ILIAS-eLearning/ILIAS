<?php

class ilIntroduceEventHandlingArtifactDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    /**
     * @inheritDoc
     */
    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->dropTable("il_event_handling");
    }
}