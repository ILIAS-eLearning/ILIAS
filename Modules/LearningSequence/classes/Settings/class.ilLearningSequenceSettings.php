<?php declare(strict_types=1);

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
 * Settings for an LSO (like abstract, extro)
 */
class ilLearningSequenceSettings
{
    protected int $obj_id;
    protected string $abstract;
    protected string $extro;
    protected ?string $abstract_image;
    protected ?string $extro_image;

    /**
     * @return array<string, array>
     */
    protected array $uploads = [];

    /**
     * @var string[]
     */
    protected array $deletions = [];
    protected bool $members_gallery;

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

    public function getAbstractImage() : string
    {
        return $this->abstract_image ?? '';
    }

    public function withAbstractImage(string $path = null) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->abstract_image = $path;
        return $clone;
    }

    public function getExtroImage() : string
    {
        return $this->extro_image ?? '';
    }

    public function withExtroImage(string $path = null) : ilLearningSequenceSettings
    {
        $clone = clone $this;
        $clone->extro_image = $path;
        return $clone;
    }

    /**
     * @return array<string, array>
     */
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

    /**
     * @return string[]
     */
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
