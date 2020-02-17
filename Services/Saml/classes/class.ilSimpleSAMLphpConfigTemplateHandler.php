<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * Class ilSimpleSAMLphpConfigTemplateHandler
 */
class ilSimpleSAMLphpConfigTemplateHandler
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * ilSimpleSAMLphpConfigTemplateHandler constructor.
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @param array $placeholders A key value map where the key should be the name of a placeholder, and the value is a primitive type or a callable
     */
    public function copy(string $sourcePath, string $destinationPath, array $placeholders = []) : void
    {
        if (!$this->fs->has($destinationPath)) {
            $templateContents = file_get_contents($sourcePath);

            foreach ($placeholders as $placeholder => $value) {
                if (is_callable($value)) {
                    $value = $value();
                }

                $templateContents = str_replace('[[' . $placeholder . ']]', $value, $templateContents);
            }

            // Does not work because of .sec renaming of PHP files
            //$this->fs->put($destinationPath, $templateContents);
            file_put_contents(ilUtil::getDataDir() . '/' . $destinationPath, $templateContents);
        }
    }
}
