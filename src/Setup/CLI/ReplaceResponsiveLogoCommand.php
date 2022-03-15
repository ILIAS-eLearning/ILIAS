<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use DirectoryIterator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ReplaceResponsiveLogoCommand extends Command
{
    protected const RESPONSIVE_LOGO_PATH = '/images/HeaderIconResponsive.svg';
    protected const COMMON_LOGO_PATH = '/images/HeaderIcon.svg';

    protected static $defaultName = 'replace-responsive-logo';
    protected ?string $delos_responsive_logo_hash;
    protected ?string $delos_common_logo_hash;
    protected string $delos_path;
    protected string $ilias_path;

    public function __construct(string $name = null)
    {
        // determine ilias and delos-skin paths.
        $this->ilias_path = dirname(__FILE__, 4);
        $this->delos_path = "$this->ilias_path/templates/default/";

        // calculate original logo hashes.
        $this->delos_responsive_logo_hash = $this->getHash($this->delos_path . self::RESPONSIVE_LOGO_PATH);
        $this->delos_common_logo_hash = $this->getHash($this->delos_path . self::COMMON_LOGO_PATH);

        parent::__construct($name);
    }

    public function configure() : void
    {
        $this->setDescription("Replaces the HeaderIconResponsive.svg in custom skins where necessary.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io_wrapper = new IOWrapper($input, $output);
        $io_wrapper->title("Replacing responsive header-icons where necessary.");

        // abort if the header-icons of the delos skin could not be located.
        if (null === $this->delos_common_logo_hash || null === $this->delos_responsive_logo_hash) {
            $io_wrapper->error("Could not locate header-icons in '$this->delos_path/images'.");
            return 1;
        }

        $skin_directory = "$this->ilias_path/Customizing/global/skin/";

        // nothing to do if no custom skins were installed/created.
        if (!file_exists($skin_directory) || !is_dir($skin_directory)) {
            $io_wrapper->success("No custom skins installed or created, nothing to do.");
            return 0;
        }

        foreach (new DirectoryIterator($skin_directory) as $skin_name) {
            if ('.' === $skin_name || '..' === $skin_name) {
                continue;
            }

            $this->maybeReplaceResponsiveIcon($skin_directory . $skin_name);
        }

        $io_wrapper->success("Replaced all necessary responsive header-icons.");
        return 0;
    }

    /**
     * Replaces the responsive header-icon with the common header-icon, if the
     * common icon is different from the ilias icon but the responsive icon is not.
     */
    protected function maybeReplaceResponsiveIcon(string $skin_path) : void
    {
        $responsive_logo = $skin_path . self::RESPONSIVE_LOGO_PATH;
        $common_logo = $skin_path . self::COMMON_LOGO_PATH;

        if ($this->getHash($common_logo) !== $this->delos_common_logo_hash &&
            $this->getHash($responsive_logo) === $this->delos_responsive_logo_hash
        ) {
            copy($common_logo, $responsive_logo);
        }
    }

    /**
     * Returns the sha1-sum of the given file if it exists.
     */
    protected function getHash(string $absolute_file_path) : ?string
    {
        if (!file_exists($absolute_file_path)) {
            return null;
        }

        if (false !== ($hash = sha1_file($absolute_file_path))) {
            return $hash;
        }

        return null;
    }
}