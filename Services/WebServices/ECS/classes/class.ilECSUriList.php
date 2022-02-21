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
 * Presentation of ecs uril (http://...campusconnect/courselinks)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSUriList
{
    public array $uris = array();

    /**
     * Constructor
     */
    public function __construct()
    {
    }


    /**
     * Add uri
     * @param  string $a_uri
     * @param int $a_link_id
     */
    public function add($a_uri, $a_link_id)
    {
        $this->uris[$a_link_id] = $a_uri;
    }

    /**
     * Get link ids
     * @return <type>
     */
    public function getLinkIds()
    {
        return (array) array_keys($this->uris);
    }

    /**
     * Get uris
     * @return array
     */
    public function getUris()
    {
        return (array) $this->uris;
    }
}
