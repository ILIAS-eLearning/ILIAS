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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilAssOrderingFormValuesObjectsConverter implements ilFormValuesManipulator
{
    public const INDENTATIONS_POSTVAR_SUFFIX = '_ordering';
    public const INDENTATIONS_POSTVAR_SUFFIX_JS = '__default';

    public const CONTEXT_MAINTAIN_ELEMENT_TEXT = 'maintainItemText';
    public const CONTEXT_MAINTAIN_ELEMENT_IMAGE = 'maintainItemImage';
    public const CONTEXT_MAINTAIN_HIERARCHY = 'maintainHierarchy';

    /**
     * @var string
     */
    protected $context = null;

    /**
     * @var string
     */
    protected $postVar = null;

    /**
     * @var string
     */
    protected $imageRemovalCommand = null;

    /**
     * @var string
     */
    protected $imageUrlPath;

    /**
     * @var string
     */
    protected $imageFsPath;

    /**
     * @var string
     */
    protected $thumbnailPrefix;

    private readonly RequestDataCollector $request_data_collector;

    public function __construct()
    {
        $local_dic = QuestionPoolDIC::dic();
        $this->request_data_collector = $local_dic['request_data_collector'];
    }

    /**
     * @return string
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @param $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getPostVar(): ?string
    {
        return $this->postVar;
    }

    /**
     * @param $postVar
     */
    public function setPostVar($postVar): void
    {
        $this->postVar = $postVar;
    }

    public function getImageRemovalCommand(): ?string
    {
        return $this->imageRemovalCommand;
    }

    public function setImageRemovalCommand($imageRemovalCommand): void
    {
        $this->imageRemovalCommand = $imageRemovalCommand;
    }

    public function getImageUrlPath(): string
    {
        return $this->imageUrlPath;
    }

    /**
     * @param string $imageUrlPath
     */
    public function setImageUrlPath($imageUrlPath): void
    {
        $this->imageUrlPath = $imageUrlPath;
    }

    /**
     * @return string
     */
    public function getImageFsPath(): string
    {
        return $this->imageFsPath;
    }

    /**
     * @param string $imageFsPath
     */
    public function setImageFsPath($imageFsPath): void
    {
        $this->imageFsPath = $imageFsPath;
    }

    /**
     * @return string
     */
    public function getThumbnailPrefix(): string
    {
        return $this->thumbnailPrefix;
    }

    /**
     * @param string $thumbnailPrefix
     */
    public function setThumbnailPrefix($thumbnailPrefix): void
    {
        $this->thumbnailPrefix = $thumbnailPrefix;
    }

    public function getIndentationsPostVar(): string
    {
        $postVar = $this->getPostVar();
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX;
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX_JS;

        return $postVar;
    }

    protected function needsConvertToValues($elements_or_values): bool
    {
        if (!count($elements_or_values)) {
            return false;
        }

        return (current($elements_or_values) instanceof ilAssOrderingElement);
    }

    public function manipulateFormInputValues(array $input_values): array
    {
        if ($this->needsConvertToValues($input_values)) {
            $input_values = $this->collectValuesFromElements($input_values);
        }

        return $input_values;
    }

    protected function collectValuesFromElements(array $elements): array
    {
        $values = [];

        foreach ($elements as $identifier => $ordering_element) {
            switch ($this->getContext()) {
                case self::CONTEXT_MAINTAIN_ELEMENT_TEXT:

                    $values[$identifier] = $this->getTextContentValueFromObject($ordering_element);
                    break;

                case self::CONTEXT_MAINTAIN_ELEMENT_IMAGE:

                    $values[$identifier] = $this->getImageContentValueFromObject($ordering_element);
                    break;

                case self::CONTEXT_MAINTAIN_HIERARCHY:

                    $values[$identifier] = $this->getStructValueFromObject($ordering_element);
                    break;

                default:
                    throw new ilFormException('unsupported context: ' . $this->getContext());
            }
        }

        return $values;
    }

    protected function getTextContentValueFromObject(ilAssOrderingElement $element): ?string
    {
        return $element->getContent();
    }

    protected function getImageContentValueFromObject(ilAssOrderingElement $element): array
    {
        $element->setImagePathWeb($this->getImageUrlPath());
        $element->setImagePathFs($this->getImageFsPath());
        $element->setImageThumbnailPrefix($this->getThumbnailPrefix());

        return [
            'title' => $element->getContent(),
            'src' => $element->getPresentationImageUrl()
        ];
    }

    protected function getStructValueFromObject(ilAssOrderingElement $element): array
    {
        return [
            'answer_id' => $element->getId(),
            'random_id' => $element->getRandomIdentifier(),
            'content' => (string) $element->getContent(),
            'ordering_position' => $element->getPosition(),
            'ordering_indentation' => $element->getIndentation()
        ];
    }

    protected function needsConvertToElements($values_or_elements): bool
    {
        if (!count($values_or_elements)) {
            return false;
        }

        return !(current($values_or_elements) instanceof ilAssOrderingElement);
    }

    public function manipulateFormSubmitValues(array $submit_values): array
    {
        if ($this->needsConvertToElements($submit_values)) {
            $submit_values = $this->constructElementsFromValues($submit_values);
        }

        return $submit_values;
    }

    public function constructElementsFromValues(array $values): array
    {
        $elements = [];

        $content = $values;
        if (array_key_exists('content', $values)) {
            $content = $values['content'];
        }

        $position = [];
        if (array_key_exists('position', $values)) {
            $position = $values['position'];
        }

        $indentation = [];
        if (array_key_exists('indentation', $values)) {
            $indentation = $values['indentation'];
        }

        $counter = 0;
        foreach ($content as $identifier => $value) {
            $element = new ilAssOrderingElement();

            $element->setRandomIdentifier((int) $identifier);
            $element->setPosition((int) ($position[$identifier] ?? $counter));
            $element->setContent($value);
            $element->setIndentation((int) ($indentation[$identifier] ?? 0));

            if ($this->getContext() === self::CONTEXT_MAINTAIN_ELEMENT_IMAGE) {
                $element->setUploadImageName($this->fetchSubmittedImageFilename($identifier));
                $element->setUploadImageFile($this->fetchSubmittedUploadFilename($identifier));

                $element->setImageRemovalRequest($this->wasImageRemovalRequested($identifier));
            }

            $elements[$identifier] = $element;
            $counter++;
        }

        return $elements;
    }

    protected function fetchSubmittedImageFilename($identifier)
    {
        $fileUpload = $this->fetchElementFileUpload($identifier);
        return $this->fetchSubmittedFileUploadProperty($fileUpload, 'name');
    }

    protected function fetchSubmittedUploadFilename($identifier)
    {
        $fileUpload = $this->fetchElementFileUpload($identifier);
        return $this->fetchSubmittedFileUploadProperty($fileUpload, 'tmp_name');
    }

    protected function fetchSubmittedFileUploadProperty(mixed $file_upload, string $property)
    {
        return $file_upload[$property] ?? null;
    }

    protected function fetchElementFileUpload($identifier)
    {
        return $this->fetchSubmittedUploadFiles()[$identifier] ?? [];
    }

    protected function fetchSubmittedUploadFiles(): array
    {
        $submitted_upload_files = $this->getFileSubmitDataRestructuredByIdentifiers();
        //$submittedUploadFiles = $this->getFileSubmitsHavingActualUpload($submittedUploadFiles);
        return $submitted_upload_files;
    }

    protected function getFileSubmitsHavingActualUpload(array $submitted_upload_files): array
    {
        foreach ($submitted_upload_files as $identifier => $upload_properties) {
            if (!isset($upload_properties['tmp_name'])) {
                unset($submitted_upload_files[$identifier]);
                continue;
            }

            if ($upload_properties['tmp_name'] === '') {
                unset($submitted_upload_files[$identifier]);
                continue;
            }

            if (!is_uploaded_file($upload_properties['tmp_name'])) {
                unset($submitted_upload_files[$identifier]);
            }
        }

        return $submitted_upload_files;
    }

    /**
     * @return array
     */
    protected function getFileSubmitDataRestructuredByIdentifiers(): array
    {
        $submitted_upload_files = [];

        foreach ($this->getFileSubmitData() as $uploadProperty => $valueElement) {
            foreach ($valueElement as $element_identifier => $uploadValue) {
                if (!isset($submitted_upload_files[$element_identifier])) {
                    $submitted_upload_files[$element_identifier] = [];
                }

                $submitted_upload_files[$element_identifier][$uploadProperty] = $uploadValue;
            }
        }

        return $submitted_upload_files;
    }

    protected function getFileSubmitData(): array
    {
        return $_FILES[$this->getPostVar()] ?? [];
    }

    /**
     * TODO: Instead of accessing post, the complete ilFormValuesManipulator should be aware of a server request or the corresponding processed input values.
     * @param $identifier
     * @return bool
     */
    protected function wasImageRemovalRequested($identifier): bool
    {
        if (!$this->getImageRemovalCommand()) {
            return false;
        }

        $cmd = $this->request_data_collector->strArray('cmd', 3);

        if (!isset($cmd[$this->getImageRemovalCommand()])) {
            return false;
        }

        $field_arr = $cmd[$this->getImageRemovalCommand()];

        if (!isset($field_arr[$this->getPostVar()])) {
            return false;
        }

        return (string) str_replace(
            ilIdentifiedMultiValuesJsPositionIndexRemover::IDENTIFIER_INDICATOR_PREFIX,
            '',
            (string) key($field_arr[$this->getPostVar()])
        ) === (string) $identifier;
    }
}
