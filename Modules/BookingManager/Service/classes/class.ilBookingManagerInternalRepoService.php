<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalRepoService
{
    /**
     * @var ilBookingManagerInternalDataService
     */
    protected $data_sercice;

    /**
     * Constructor
     */
    public function __construct(ilBookingManagerInternalDataService $data_sercice)
    {
        $this->data_sercice = $data_sercice;
    }

    /**
     * @return ilBookingPreferencesDBRepository
     */
    public function getPreferencesRepo()
    {
        return new ilBookingPreferencesDBRepository($this->data_sercice->preferencesFactory());
    }

    /**
     * @return ilBookingPrefBasedBookGatewayRepository
     */
    public function getPreferenceBasedBookingRepo()
    {
        return new ilBookingPrefBasedBookGatewayRepository();
    }


}