<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilSamlAuth
 */
interface ilSamlIdpDiscovery
{
    /**
     * This method should return an array of IDPs. Each element should be an array as well, providing at least a value for key 'entityid'.
     * @return array
     */
    public function getList();

    /**
     * @param int $idpId
     * @param string $metadata
     */
    public function storeIdpMetadata($idpId, $metadata);

    /**
     * @param int $idpId
     */
    public function fetchIdpMetadata($idpId);

    /**
     * @param int $idpId
     */
    public function deleteIdpMetadata($idpId);
}
