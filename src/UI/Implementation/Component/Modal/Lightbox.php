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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Modal\LightboxPage;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Lightbox extends Modal implements Component\Modal\Lightbox
{
    private const SCHEME_BRIGHT = 'bright';
    private const SCHEME_DARK = 'dark';
    /**
     * @var LightboxPage[]
     */
    protected array $pages;

    protected string $scheme = self::SCHEME_BRIGHT;

    /**
     * @param LightboxPage|LightboxPage[] $pages
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct($pages, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        $pages = $this->toArray($pages);
        $types = array(LightboxPage::class);
        $this->checkArgListElements('pages', $pages, $types);
        // if there is at least one image page, the lightbox is in dark mode.
        array_walk($pages, function (LightboxPage $page) {
            if ($page instanceof Component\Modal\LightboxImagePage) {
                $this->scheme = self::SCHEME_DARK;
            }
        });
        $this->pages = $pages;
    }

    /**
     * @inheritdoc
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
}
