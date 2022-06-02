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
 
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Modal\LightboxPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Modal\LightboxTextPage as ILightboxTextPage;

/**
 * Class LightboxTextPage
 * @package ILIAS\UI\Implementation\Component\Modal
 * @author Michael Jansen <mjansen@databay.de>
 */
class LightboxTextPage implements LightboxPage, ILightboxTextPage
{
    use ComponentHelper;

    protected string $text;
    protected string $title;

    public function __construct(string $text, string $title)
    {
        $this->text = $text;
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getComponent() : C\Legacy\Legacy
    {
        return new Legacy($this->text, new SignalGenerator());
    }
}
