<?php

declare(strict_types=1);

namespace ILIAS\TestQuestionPool;

class QuestionFilesService
{
    protected static array $allowedImageMaterialFileExtensionsByMimeType = array(
        'image/jpeg' => array('jpg', 'jpeg'),
        'image/png' => array('png'),
        'image/gif' => array('gif')
    );

    /**
     * @return array	all allowed file extensions for image material
     */
    public static function getAllowedImageMaterialFileExtensions(): array
    {
        $extensions = array();

        foreach (self::$allowedImageMaterialFileExtensionsByMimeType as $mimeType => $mimeExtensions) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $extensions = array_merge($extensions, $mimeExtensions);
        }
        return array_unique($extensions);
    }

    public const IMG_MIME_TYPE_JPG = 'image/jpeg';
    public const IMG_MIME_TYPE_PNG = 'image/png';
    public const IMG_MIME_TYPE_GIF = 'image/gif';

    protected static array $allowedFileExtensionsByMimeType = array(
        self::IMG_MIME_TYPE_JPG => array('jpg', 'jpeg'),
        self::IMG_MIME_TYPE_PNG => array('png'),
        self::IMG_MIME_TYPE_GIF => array('gif')
    );

    protected static array $allowedCharsetsByMimeType = array(
        self::IMG_MIME_TYPE_JPG => array('binary'),
        self::IMG_MIME_TYPE_PNG => array('binary'),
        self::IMG_MIME_TYPE_GIF => array('binary')
    );

    public function getAllowedFileExtensionsForMimeType(string $mimeType): array
    {
        foreach (self::$allowedFileExtensionsByMimeType as $allowedMimeType => $extensions) {
            $rexCharsets = implode('|', self::$allowedCharsetsByMimeType[$allowedMimeType]);
            $rexMimeType = preg_quote($allowedMimeType, '/');

            $rex = '/^' . $rexMimeType . '(;(\s)*charset=(' . $rexCharsets . '))*$/';

            if (!preg_match($rex, $mimeType)) {
                continue;
            }

            return $extensions;
        }

        return array();
    }

    public function isAllowedImageMimeType($mimeType): bool
    {
        return (bool) count($this->getAllowedFileExtensionsForMimeType($mimeType));
    }

    public function isAllowedImageFileExtension(string $mimeType, string $fileExtension): bool
    {
        return in_array(strtolower($fileExtension), $this->getAllowedFileExtensionsForMimeType($mimeType), true);
    }

    public function buildImagePath($questionId, $parentObjectId): string
    {
        return CLIENT_WEB_DIR . '/assessment/' . $parentObjectId . '/' . $questionId . '/images/';
    }
}
