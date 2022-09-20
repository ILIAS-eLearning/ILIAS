<?php

declare(strict_types=1);

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
*/
class ilECSEvent
{
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DESTROYED = 'destroyed';
    public const NEW_EXPORT = 'new_export';

    protected object $json_obj;
    public string $status = '';
    public string $ressource = '';
    public int $ressource_id = 0;
    public ?string $ressource_type = '';

    /**
     * @param object json object
     */
    public function __construct($json_obj)
    {
        $this->json_obj = $json_obj;
        $this->read();
    }

    /**
     * get title
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * getDescription
     */
    public function getRessource(): string
    {
        return $this->ressource;
    }

    /**
     * Get ressource id
     */
    public function getRessourceId(): int
    {
        return $this->ressource_id;
    }


    /**
     * Get ressource type
     */
    public function getRessourceType(): ?string
    {
        return $this->ressource_type;
    }


    /**
     * Read community entries and participants
     */
    private function read(): void
    {
        $this->status = $this->json_obj->status;
        $this->ressource = $this->json_obj->ressource;

        $res_arr = (array) explode('/', $this->getRessource());

        $this->ressource_id = array_pop($res_arr);
        $this->ressource_type = array_pop($res_arr);
    }
}
