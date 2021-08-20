<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceAcceptanceDataGateway
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceAcceptanceDataGateway
{
    public function trackAcceptance(ilTermsOfServiceAcceptanceEntity $entity) : void;

    public function loadCurrentAcceptanceOfUser(
        ilTermsOfServiceAcceptanceEntity $entity
    ) : ilTermsOfServiceAcceptanceEntity;

    public function loadById(ilTermsOfServiceAcceptanceEntity $entity) : ilTermsOfServiceAcceptanceEntity;

    public function deleteAcceptanceHistoryByUser(ilTermsOfServiceAcceptanceEntity $entity) : void;
}
