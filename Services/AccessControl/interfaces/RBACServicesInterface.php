<?php

namespace ILIAS\Services\RBAC;


/**
 * Provides fluid interface to RBAC services.
 */
interface RBACServicesInterface
{
    /**
     * Get the interface to the RBAC system.
     */
    public function system(): \ilRbacSystem;
    
    /**
     * Get the interface to insert relations into the RBAC system.
     */
    public function admin(): \ilRbacAdmin;
    
    /**
     * Get the interface to query the RBAC system.
     */
    public function review(): \ilRbacReview;
}
