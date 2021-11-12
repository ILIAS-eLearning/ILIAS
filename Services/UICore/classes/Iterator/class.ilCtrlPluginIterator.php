<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlPluginIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPluginIterator implements Iterator
{
    /**
     * @var string absolute path to the ILIAS plugin directory.
     */
    public const DEFAULT_ILIAS_PLUGIN_DIR = __DIR__ . '/../../../../Customizing/global/plugins/';

    /**
     * @var Iterator
     */
    private Iterator $iterator;

    /**
     * ilCtrlPluginIterator Constructor
     *
     * @param string|null $plugin_dir
     */
    public function __construct(string $plugin_dir = null)
    {
        $this->iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $plugin_dir ?? self::DEFAULT_ILIAS_PLUGIN_DIR,
                FilesystemIterator::SKIP_DOTS
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function current() : ?string
    {
        if (null !== $this->key()) {
            /** @var $file_info SplFileInfo */
            $file_info = $this->iterator->current();
            return $file_info->getPath();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        $this->iterator->next();
    }

    /**
     * @inheritDoc
     */
    public function key() : ?string
    {
        if (!$this->iterator->valid()) {
            return null;
        }

        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        $file_path = $file_info->getPath();

        try {
            require $file_info->getPath() . '/' . $file_info->getFilename();
            if (!isset($id)) {
                return null;
            }
        } catch (Throwable $t) {
            return null;
        }

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        if (!$this->iterator->valid()) {
            return false;
        }

        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        if ('plugin.php' !== $file_info->getFilename()) {
            $this->next();
            if ($this->iterator->valid()) {
                return $this->valid();
            }

            return false;
        }

        if (null === $this->key()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        $this->iterator->rewind();
    }
}