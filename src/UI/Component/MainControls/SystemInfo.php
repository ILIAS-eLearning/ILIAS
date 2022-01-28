<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface SystemInfo
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SystemInfo extends Component, JavaScriptBindable, Triggerable
{
    public const DENOTATION_NEUTRAL = 'neutral';
    public const DENOTATION_IMPORTANT = 'important';
    public const DENOTATION_BREAKING = 'breaking';

    public function getHeadLine() : string;

    public function getInformationText() : string;

    public function withDismissAction(?URI $uri) : SystemInfo;

    public function isDismissable() : bool;

    public function getDismissAction() : URI;

    /**
     * Must be one of
     * - SystemInfo::DENOTATION_NEUTRAL
     * - SystemInfo::DENOTATION_IMPORTANT
     * - SystemInfo::DENOTATION_BREAKING
     */
    public function withDenotation(string $denotation) : SystemInfo;

    public function getDenotation() : string;

    public function getCloseSignal() : Signal;
}
