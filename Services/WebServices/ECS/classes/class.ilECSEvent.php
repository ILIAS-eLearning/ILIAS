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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSEvent
{
    const CREATED = 'created';
    const UPDATED = 'updated';
    const DESTROYED = 'destroyed';
    const NEW_EXPORT = 'new_export';

    protected $json_obj = null;
    public string $status = '';
    public string $ressource = '';
    public $ressource_id = 0;
    public ?string $ressource_type = '';
    
    /**
     * Constructor
     *
     * @access public
     * @param object json object
     *
     */
    public function __construct($json_obj)
    {
        $this->json_obj = $json_obj;
        $this->read();
    }
    
    /**
     * get title
     *
     * @access public
     *
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * getDescription
     *
     * @access public
     *
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * Get ressource id
     */
    public function getRessourceId()
    {
        return $this->ressource_id;
    }


    /**
     * Get ressource type
     * @return string
     */
    public function getRessourceType()
    {
        return $this->ressource_type;
    }

    
    /**
     * Read community entries and participants
     *
     * @access private
     *
     */
    private function read()
    {
        $this->status = $this->json_obj->status;
        $this->ressource = $this->json_obj->ressource;

        $res_arr = (array) explode('/', $this->getRessource());

        $this->ressource_id = array_pop($res_arr);
        $this->ressource_type = array_pop($res_arr);
    }
}
