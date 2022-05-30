<?php

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
 * Class ilMemcacheServer
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcacheServer extends ActiveRecord
{
    /**
     * @var int
     */
    const STATUS_INACTIVE = -1;
    /**
     * @var int
     */
    const STATUS_ACTIVE = 1;

    /**
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName() : string
    {
        return 'il_gc_memcache_server';
    }

    public function isActive() : bool
    {
        return $this->getStatus() === self::STATUS_ACTIVE;
    }

    public function isReachable() : bool
    {
        $mem = new Memcached();
        $mem->resetServerList();
        $mem->addServer($this->getHost(), $this->getPort(), $this->getWeight());

        $stats = $mem->getStats();

        return $stats[$this->getHost() . ':' . $this->getPort()]['pid'] > 0;
    }

    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected ?int $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $status = self::STATUS_INACTIVE;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $host = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $port = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected int $weight = 100;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $flush_needed = false;

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function setHost(string $host) : void
    {
        $this->host = $host;
    }

    public function getPort() : int
    {
        return $this->port;
    }

    public function setPort(int $port) : void
    {
        $this->port = $port;
    }

    public function getFlushNeeded() : string
    {
        return $this->flush_needed;
    }

    public function setFlushNeeded(string $flush_needed) : void
    {
        $this->flush_needed = $flush_needed;
    }

    public function getWeight() : int
    {
        return $this->weight;
    }

    public function setWeight(int $weight) : void
    {
        $this->weight = $weight;
    }
}
