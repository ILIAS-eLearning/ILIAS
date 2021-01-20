<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class RevisionCollection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RevisionCollection
{

    /**
     * @var FileRevision[]
     */
    private $revisions = [];
    /**
     * @var ResourceIdentification
     */
    private $identification;

    /**
     * RevisionCollection constructor.
     * @param FileRevision[]         $revisions
     * @param ResourceIdentification $identification
     */
    public function __construct(ResourceIdentification $identification, array $revisions = [])
    {
        $this->identification = $identification;
        $this->revisions = $revisions;
    }

    public function add(Revision $revision) : void
    {
        if ($this->identification->serialize() !== $revision->getIdentification()->serialize()) {
            throw new NonMatchingIdentificationException("Can't add Revision since it's not the same ResourceIdentification");
        }
        foreach ($this->revisions as $r) {
            if ($r->getVersionNumber() === $revision->getVersionNumber()) {
                throw new RevisionExistsException(sprintf("Can't add already existing version number: %s",
                    $revision->getVersionNumber()));
            }
        }
        $this->revisions[$revision->getVersionNumber()] = $revision;
        asort($this->revisions);
    }

    public function remove(Revision $revision) : void
    {
        foreach ($this->revisions as $k => $revision_e) {
            if ($revision->getVersionNumber() === $revision_e->getVersionNumber()) {
                unset($this->revisions[$k]);

                return;
            }
        }
    }

    public function replaceSingleRevision(Revision $revision) : void
    {
        foreach ($this->revisions as $k => $revision_e) {
            if ($revision_e->getVersionNumber() === $revision->getVersionNumber()) {
                $this->revisions[$k] = $revision;
            }
        }
    }

    public function replaceAllRevisions(Revision $revision) : void
    {
        $this->revisions = [];
        $this->add($revision);
    }

    public function getCurrent() : Revision
    {
        $v = array_values($this->revisions);
        sort($v);
        $current = end($v);
        if (!$current instanceof Revision) {
            $current = new NullRevision($this->identification);
        }

        return $current;
    }

    /**
     * @return Revision[]
     */
    public function getAll() : array
    {
        return $this->revisions;
    }

    public function getMax() : int
    {
        if (count($this->revisions) === 0) {
            return 0;
        }
        return max(array_keys($this->revisions));
    }
}
