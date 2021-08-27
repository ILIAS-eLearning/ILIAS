<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveAction
{
    private ilDBInterface $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function isObjectActive($objId) : bool
    {
        $sql = 'SELECT obj_id FROM il_certificate WHERE obj_id = ' . $this->database->quote($objId, 'integer');

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            return true;
        }

        return false;
    }
}
