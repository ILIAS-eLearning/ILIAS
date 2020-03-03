<?php

declare(strict_types=1);

/**
 * Settings for an LSO (like abstract, extro)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceSettings
{

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $abstract;

    /**
     * @var string
     */
    protected $extro;

    /**
     * @var string|null
     */
    protected $abstract_image;

    /**
     * @var string|null
     */
    protected $extro_image;

    /**
     * @var array
     */
    protected $uploads = [];

    /**
     * @var array
     */
    protected $deletions = [];

    /**
     * @var bool
     */
    protected $members_gallery;


    public function __construct(
        int $obj_id,
        string $abstract = '',
        string $extro = '',
        string $abstract_image = null,
        string $extro_image = null,
        bool $members_gallery = false
    ) {
        $this->obj_id = $obj_id;
        $this->abstract = $abstract;
        $this->extro = $extro;
        $this->abstract_image = $abstract_image;
        $this->extro_image = $extro_image;
        $this->members_gallery = $members_gallery;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getAbstract() : string
    {
        return $this->abstract;
    }

    public function withAbstract(string $abstract) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->abstract = $abstract;
        return $clone;
    }

    public function getExtro() : string
    {
        return $this->extro;
    }

    public function withExtro(string $extro) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->extro = $extro;
        return $clone;
    }

    public function getAbstractImage()
    {
        return $this->abstract_image;
    }

    public function withAbstractImage(string $path=null) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->abstract_image = $path;
        return $clone;
    }

    public function getExtroImage()
    {
        return $this->extro_image;
    }

    public function withExtroImage(string $path=null) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->extro_image = $path;
        return $clone;
    }

    public function getUploads() : array
    {
        return $this->uploads;
    }

    public function withUpload(array $upload_info, string $which) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->uploads[$which] = $upload_info;
        return $clone;
    }

    public function getDeletions() : array
    {
        return $this->deletions;
    }

    public function withDeletion(string $which) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->deletions[] = $which;
        return $clone;
    }

    public function getMembersGallery() : bool
    {
        return $this->members_gallery;
    }

    public function withMembersGallery(bool $members_gallery) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->members_gallery = $members_gallery;
        return $clone;
    }
}
