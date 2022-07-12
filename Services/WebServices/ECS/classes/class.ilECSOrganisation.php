<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSOrganisation
{
    protected string $name;
    protected string $abbr;

    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
    }

    /**
     * load from json
     *
     * @param object json representation
     * @throws ilException
     */
    public function loadFromJson($a_json) : void
    {
        if (!is_object($a_json)) {
            $this->logger->warning(__METHOD__ . ': Cannot load from JSON. No object given.');
            throw new ilException('Cannot parse ECSParticipant.');
        }
        $this->name = $a_json->name;
        $this->abbr = $a_json->abbr;
    }

    /**
     * Get name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get abbreviation
     */
    public function getAbbreviation() : string
    {
        return $this->abbr;
    }
}
