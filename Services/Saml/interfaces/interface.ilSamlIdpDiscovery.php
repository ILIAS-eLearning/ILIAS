<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Interface ilSamlAuth
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilSamlIdpDiscovery
{
    /**
     * This method should return an array of IDPs. Each element should be an array as well, providing at least a value for key 'entityid'.
     * @return array
     */
    public function getList() : array;

    /**
     * @param int $idpId
     * @param string $metadata
     */
    public function storeIdpMetadata(int $idpId, string $metadata) : void;

    /**
     * @param int $idpId
     * @return string
     */
    public function fetchIdpMetadata(int $idpId) : string;

    /**
     * @param int $idpId
     */
    public function deleteIdpMetadata(int $idpId) : void;
}
