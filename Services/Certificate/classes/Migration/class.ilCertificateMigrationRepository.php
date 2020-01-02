<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationRepository
{
    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @param ilDBInterface $database
     * @param ilLogger $logger
     */
    public function __construct(ilDBInterface $database, ilLogger $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * @param int $userId
     */
    public function deleteFromMigrationJob(int $userId)
    {
        $this->logger->log(sprintf('START - Delete all certificate migration jobs for user(user_id: "%s")', $userId));
        $sql = 'DELETE FROM il_cert_bgtask_migr WHERE usr_id = ' . $this->database->quote($userId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->log(sprintf('END - Delete all certificate migration jobs for user(user_id: "%s")', $userId));
    }
}
