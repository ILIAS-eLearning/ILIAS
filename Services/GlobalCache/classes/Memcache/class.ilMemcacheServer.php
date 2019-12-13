<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilMemcacheServer
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcacheServer extends ActiveRecord
{
    const STATUS_INACTIVE = -1;
    const STATUS_ACTIVE = 1;


    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return 'il_gc_memcache_server';
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }


    /**
     * @return bool
     */
    public function isReachable()
    {
        $mem = new Memcached();
        $mem->resetServerList();
        $mem->addServer($this->getHost(), $this->getPort(), $this->getWeight());
        $stats = $mem->getStats();

        return $stats[$this->getHost() . ':' . $this->getPort()]['pid'] > 0;
    }


    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $status = self::STATUS_INACTIVE;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $host = '';
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $port = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected $weight = 100;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $flush_needed = false;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }


    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }


    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }


    /**
     * @return string
     */
    public function getFlushNeeded()
    {
        return $this->flush_needed;
    }


    /**
     * @param string $flush_needed
     */
    public function setFlushNeeded($flush_needed)
    {
        $this->flush_needed = $flush_needed;
    }


    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }


    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}
