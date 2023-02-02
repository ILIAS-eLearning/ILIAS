<?php

declare(strict_types=1);

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
* Class represents an ordering element for assOrderingQuestion
*
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
* @package		Modules/TestQuestionPool
*/
class ilAssOrderingElement
{
    public const EXPORT_IDENT_PROPERTY_SEPARATOR = '_';

    public static $objectInstanceCounter = 0;
    public $objectInstanceId;

    /**
     * this identifier equals the database's row id
     * @var integer
     */
    public int $id;

    /**
     * this identifier is generated randomly
     * it is recycled for known elements
     *
     * the main purpose is to have a key that does not make the solution
     * derivable and is therefore useable in the examines working form
     */
    protected ?int $random_identifier = null;

    /**
     * this identifier is used to identify elements and is stored
     * together with the set position and indentation
     *
     * this happens for the examine's submit data as well, an order index
     * is build and the elements are assigned using this identifier
     *
     * it is an integer sequence starting at 0 that increments
     * with every added element while obsolete numbers are not recycled
     */
    protected ?int $solution_identifier = null;

    /**
     * the correct width of indentation for the element
     */
    protected int $indentation = 0;

    /**
     * the correct position in the ordering sequence
     */
    protected ?int $position = null;
    protected ?string $content = null;
    protected ?string $uploadImageName = null;
    protected ?string $uploadImageFile = null;
    protected bool $imageRemovalRequest = false;
    protected ?string $imagePathWeb = null;
    protected ?string $imagePathFs = null;
    protected $imageThumbnailPrefix = null;

    /**
     * ilAssOrderingElement constructor.
     */
    public function __construct(int $id = -1)
    {
        $this->id = $id;
        $this->objectInstanceId = ++self::$objectInstanceCounter;
    }

    /**
     * Cloning
     */
    public function __clone()
    {
        $this->objectInstanceId = ++self::$objectInstanceCounter;
    }

    public function getClone(): ilAssOrderingElement
    {
        return clone $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRandomIdentifier(): ?int
    {
        return $this->random_identifier;
    }

    public function setRandomIdentifier(int $random_identifier): void
    {
        $this->random_identifier = $random_identifier;
    }

    /**
     * @return int
     */
    public function getSolutionIdentifier(): ?int
    {
        return $this->solution_identifier;
    }

    public function setSolutionIdentifier(int $solution_identifier): void
    {
        $this->solution_identifier = $solution_identifier;
    }

    public function setIndentation(int $indentation): void
    {
        $this->indentation = $indentation;
    }

    public function getIndentation(): int
    {
        return $this->indentation;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }


    public function setContent($content): void
    {
        if (is_array($content)) {
            $content = array_shift($content);
        }
        $this->content = $content;
    }

    public function getUploadImageFile(): ?string
    {
        return $this->uploadImageFile;
    }

    /**
     * @param string $uploadImageFile
     */
    public function setUploadImageFile($uploadImageFile): void
    {
        if (is_array($uploadImageFile)) {
            $uploadImageFile = array_shift($uploadImageFile);
        }
        $this->uploadImageFile = $uploadImageFile;
    }

    /**
     * @return string
     */
    public function getUploadImageName(): ?string
    {
        return $this->uploadImageName;
    }

    /**
     * @param string $uploadImageName
     */
    public function setUploadImageName($uploadImageName): void
    {
        if (is_array($uploadImageName)) {
            $uploadImageName = array_shift($uploadImageName);
        }
        $this->uploadImageName = $uploadImageName;
    }

    /**
     * @return bool
     */
    public function isImageUploadAvailable(): bool
    {
        return (bool) strlen($this->getUploadImageFile());
    }

    /**
     * @return bool
     */
    public function isImageRemovalRequest(): ?bool
    {
        return $this->imageRemovalRequest;
    }

    /**
     * @param bool $imageRemovalRequest
     */
    public function setImageRemovalRequest($imageRemovalRequest): void
    {
        $this->imageRemovalRequest = $imageRemovalRequest;
    }

    /**
     * @return string
     */
    public function getImagePathWeb(): ?string
    {
        return $this->imagePathWeb;
    }

    /**
     * @param string $imagePathWeb
     */
    public function setImagePathWeb($imagePathWeb): void
    {
        $this->imagePathWeb = $imagePathWeb;
    }

    /**
     * @return string
     */
    public function getImagePathFs(): ?string
    {
        return $this->imagePathFs;
    }

    /**
     * @param string $imagePathFs
     */
    public function setImagePathFs($imagePathFs): void
    {
        $this->imagePathFs = $imagePathFs;
    }

    public function getImageThumbnailPrefix()
    {
        return $this->imageThumbnailPrefix;
    }

    public function setImageThumbnailPrefix($imageThumbnailPrefix): void
    {
        $this->imageThumbnailPrefix = $imageThumbnailPrefix;
    }

    /**
     * @param ilAssOrderingElement $element
     * @return bool
     */
    public function isSameElement(ilAssOrderingElement $element): bool
    {
        return [
            $this->getRandomIdentifier(),
            $this->getSolutionIdentifier(),
            $this->getPosition(),
            $this->getIndentation(),
        ] == [
            $element->getRandomIdentifier(),
            $element->getSolutionIdentifier(),
            $element->getPosition(),
            $element->getIndentation()
        ];
    }

    public function getStorageValue1($orderingType)
    {
        switch ($orderingType) {
            case assOrderingQuestion::OQ_NESTED_TERMS:
            case assOrderingQuestion::OQ_NESTED_PICTURES:

                return $this->getPosition();

            case assOrderingQuestion::OQ_TERMS:
            case assOrderingQuestion::OQ_PICTURES:
            default:
                return $this->getSolutionIdentifier();
        }
    }

    public function getStorageValue2($orderingType)
    {
        switch ($orderingType) {
            case assOrderingQuestion::OQ_NESTED_TERMS:
            case assOrderingQuestion::OQ_NESTED_PICTURES:

                return $this->getRandomIdentifier() . ':' . $this->getIndentation();

            case assOrderingQuestion::OQ_TERMS:
            case assOrderingQuestion::OQ_PICTURES:
            default:
                return $this->getPosition() + 1;
        }
    }

    public function __toString(): string
    {
        return $this->getContent() ?? '';
    }

    protected function thumbnailFileExists(): bool
    {
        if (!$this->getContent()) {
            return false;
        }

        return file_exists($this->getThumbnailFilePath());
    }

    protected function getThumbnailFilePath(): string
    {
        return $this->getImagePathFs() . $this->getImageThumbnailPrefix() . $this->getContent();
    }

    protected function getThumbnailFileUrl(): string
    {
        return $this->getImagePathWeb() . $this->getImageThumbnailPrefix() . $this->getContent();
    }

    protected function imageFileExists(): bool
    {
        if (!$this->getContent()) {
            return false;
        }

        return file_exists($this->getImageFilePath());
    }

    protected function getImageFilePath(): string
    {
        return $this->getImagePathFs() . $this->getContent();
    }

    protected function getImageFileUrl(): string
    {
        return $this->getImagePathWeb() . $this->getContent();
    }

    public function getPresentationImageUrl(): string
    {
        if ($this->thumbnailFileExists()) {
            return $this->getThumbnailFileUrl();
        }

        if ($this->imageFileExists()) {
            return $this->getImageFileUrl();
        }

        return '';
    }

    public function getExportIdent(): string
    {
        $ident = array(
            $this->getRandomIdentifier(),
            $this->getSolutionIdentifier(),
            $this->getPosition(),
            $this->getIndentation()
        );

        return implode(self::EXPORT_IDENT_PROPERTY_SEPARATOR, $ident);
    }

    public function isExportIdent(string $ident): bool
    {
        if (!strlen($ident)) {
            return false;
        }

        $parts = explode(self::EXPORT_IDENT_PROPERTY_SEPARATOR, $ident);
        return
            count($parts) == 4
            && ilAssOrderingElementList::isValidRandomIdentifier($parts[0])
            && ilAssOrderingElementList::isValidSolutionIdentifier($parts[1])
            && ilAssOrderingElementList::isValidPosition($parts[2])
            && ilAssOrderingElementList::isValidIndentation($parts[3]);
    }

    public function setExportIdent($ident): void
    {
        if ($this->isExportIdent($ident)) {
            list($randomId, $solutionId, $pos, $indent) = explode(
                self::EXPORT_IDENT_PROPERTY_SEPARATOR,
                $ident
            );
            $this->setRandomIdentifier((int) $randomId);
            $this->setSolutionIdentifier((int) $solutionId);
            $this->setPosition((int) $pos);
            $this->setIndentation((int) $indent);
        }
    }


    public function withRandomIdentifier(int $id): self
    {
        $clone = clone $this;
        $clone->random_identifier = $id;
        return $clone;
    }
    public function withSolutionIdentifier(int $id): self
    {
        $clone = clone $this;
        $clone->solution_identifier = $id;
        return $clone;
    }
    public function withPosition(int $position): self
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }
    public function withIndentation(int $indentation): self
    {
        $clone = clone $this;
        $clone->indentation = $indentation;
        return $clone;
    }
    public function withContent(string $content): self
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }
}
