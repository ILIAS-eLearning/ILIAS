<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveAction
{
    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @param $objId
     * @return boolean
     */
    public function isObjectActive($objId)
    {
        $sql = 'SELECT obj_id FROM il_certificate WHERE obj_id = ' . $this->database->quote($objId, 'integer');

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            return true;
        }

        return false;
    }
}
