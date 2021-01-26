<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

/**
 * Class AbstractInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
abstract class AbstractInfoResolver implements InfoResolver
{
    /**
     * @var int
     */
    protected $revision_owner_id = 0;
    /**
     * @var string
     */
    protected $revision_title = '';
    /**
     * @var int
     */
    protected $next_version_number = 0;

    /**
     * AbstractInfoResolver constructor.
     * @param int    $next_version_number
     * @param int    $revision_owner_id
     * @param string $revision_title
     */
    public function __construct(int $next_version_number, int $revision_owner_id, string $revision_title)
    {
        $this->next_version_number = $next_version_number;
        $this->revision_owner_id = $revision_owner_id;
        $this->revision_title = $revision_title;
    }

    public function getNextVersionNumber() : int
    {
        return $this->next_version_number;
    }

    public function getOwnerId() : int
    {
        return $this->revision_owner_id;
    }

    public function getRevisionTitle() : string
    {
        return $this->revision_title;
    }

}
