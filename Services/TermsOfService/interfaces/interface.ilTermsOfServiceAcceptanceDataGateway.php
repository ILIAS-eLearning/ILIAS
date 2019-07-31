<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceAcceptanceDataGateway
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceAcceptanceDataGateway
{
    /**
     * @param ilTermsOfServiceAcceptanceEntity $entity
     */
    public function trackAcceptance(ilTermsOfServiceAcceptanceEntity $entity) : void;

    /**
     * @param ilTermsOfServiceAcceptanceEntity $entity
     * @return ilTermsOfServiceAcceptanceEntity
     */
    public function loadCurrentAcceptanceOfUser(
        ilTermsOfServiceAcceptanceEntity $entity
    ) : ilTermsOfServiceAcceptanceEntity;

    /**
     * @param ilTermsOfServiceAcceptanceEntity $entity
     * @return ilTermsOfServiceAcceptanceEntity
     */
    public function loadById(ilTermsOfServiceAcceptanceEntity $entity) : ilTermsOfServiceAcceptanceEntity;

    /**
     * @param ilTermsOfServiceAcceptanceEntity $entity
     */
    public function deleteAcceptanceHistoryByUser(ilTermsOfServiceAcceptanceEntity $entity) : void;
}
