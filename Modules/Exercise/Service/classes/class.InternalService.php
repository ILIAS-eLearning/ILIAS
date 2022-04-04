<?php declare(strict_types = 1);

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
 
namespace ILIAS\Exercise;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Exercise internal service.
 * Do not use in other components.
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalService
{
    protected InternalDataService $data;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;
    protected InternalRepoService $repo;

    protected \ilDBInterface $db;
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected \ilObjectService $obj_service;


    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->db = $DIC->database();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->obj_service = $DIC->object();

        $this->data = new InternalDataService();
        $this->repo = new InternalRepoService(
            $this->data(),
            $this->db
        );
        $this->domain = new InternalDomainService(
            $this->data,
            $this->repo
        );
    }

    public function gui(
        array $query_params = null,
        array $post_data = null
    ) : InternalGUIService {
        return new InternalGUIService(
            $this,
            $this->http,
            $this->refinery,
            $query_params = null,
            $post_data = null
        );
    }

    /**
     * Booking service repos
     */
    public function repo() : InternalRepoService
    {
        return $this->repo;
    }

    /**
     * Booking service data objects
     */
    public function data() : InternalDataService
    {
        return $this->data;
    }

    public function domain() : InternalDomainService
    {
        return $this->domain;
    }
}
