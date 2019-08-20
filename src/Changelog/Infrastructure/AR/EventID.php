<?php

namespace ILIAS\Changelog\Infrastructure\AR;

use Exception;
use ILIAS\Data\UUID\Factory as UUIDFactory;

/**
 * Class EventID
 *
 * @package ILIAS\Changelog\Infrastructure\AR
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EventID
{

    /**
     * @var string
     */
    private $id;


    /**
     * EventID constructor.
     *
     * @param string|null $id
     *
     * @throws Exception
     */
    public function __construct(string $id = null)
    {
        $this->id = $id ?: (new UUIDFactory())->uuid4AsString();
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }


    /**
     * @param EventID $anId
     *
     * @return bool
     */
    public function equals(EventID $anId) : bool
    {
        return $this->getId() === $anId->getId();
    }
}