<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilStyleReplaceResponsiveLogoObjective implements Objective
{
    protected const RESPONSIVE_LOGO_PATH = '/images/HeaderIconResponsive.svg';
    protected const COMMON_LOGO_PATH = '/images/HeaderIcon.svg';

    /**
     * @var string|null
     */
    protected $delos_responsive_logo_hash;
    /**
     * @var string|null
     */
    protected $delos_common_logo_hash;
    /**
     * @var string
     */
    protected $delos_path;
    /**
     * @var string
     */
    protected $ilias_path;
    /**
     * @var string
     */
    protected $skins_path;

    public function __construct()
    {
        // determine ilias and delos-skin paths.
        $this->ilias_path = dirname(__FILE__, 5);
        $this->delos_path = "$this->ilias_path/templates/default/";
        $this->skins_path = "$this->ilias_path/Customizing/global/skin/";

        // calculate original logo hashes.
        $this->delos_responsive_logo_hash = $this->getFileHash($this->delos_path . self::RESPONSIVE_LOGO_PATH);
        $this->delos_common_logo_hash = $this->getFileHash($this->delos_path . self::COMMON_LOGO_PATH);
    }

    /**
     * @inheritDoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return 'Replacing responsive logos where necessary.';
    }

    /**
     * @inheritDoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(Environment $environment) : bool
    {
        // nothing to do if no custom skins were installed/created.
        return (file_exists($this->skins_path) && is_dir($this->skins_path));
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Environment $environment) : Environment
    {
        // abort if the header-icons of the delos skin could not be located.
        if (null === $this->delos_common_logo_hash || null === $this->delos_responsive_logo_hash) {
            return $environment;
        }

        foreach (new DirectoryIterator($this->skins_path) as $skin_name) {
            if ('.' === $skin_name || '..' === $skin_name) {
                continue;
            }

            $this->maybeReplaceResponsiveIcon($this->skins_path . $skin_name);
        }

        return $environment;
    }

    /**
     * Replaces the responsive header-icon with the common header-icon, if the
     * common icon is different from the ilias icon but the responsive icon is not.
     */
    protected function maybeReplaceResponsiveIcon(string $skin_path) : void
    {
        $responsive_logo = $skin_path . self::RESPONSIVE_LOGO_PATH;
        $common_logo = $skin_path . self::COMMON_LOGO_PATH;

        if ($this->getFileHash($common_logo) !== $this->delos_common_logo_hash &&
            $this->getFileHash($responsive_logo) === $this->delos_responsive_logo_hash
        ) {
            copy($common_logo, $responsive_logo);
        }
    }

    /**
     * Returns the sha1-sum of the given file if it exists.
     */
    protected function getFileHash(string $absolute_file_path) : ?string
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
