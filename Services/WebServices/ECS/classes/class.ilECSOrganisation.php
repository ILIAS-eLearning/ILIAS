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
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/
class ilECSOrganisation
{
    protected $json_obj;
    protected $name;
    protected $abbr;

    private ilLogger $logger;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
    }

    /**
     * load from json
     *
     * @access public
     * @param object json representation
     * @throws ilException
     */
    public function loadFromJson($a_json)
    {
        if (!is_object($a_json)) {
            $this->logger->warn(__METHOD__ . ': Cannot load from JSON. No object given.');
            throw new ilException('Cannot parse ECSParticipant.');
        }
        $this->name = $a_json->name;
        $this->abbr = $a_json->abbr;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get abbreviation
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbr;
    }
}
