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
namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class OnLoadCode
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OnLoadCode extends AbstractMedia
{
    /**
     * @var int
     */
    private $batch;

    /**
     * OnLoadCode constructor.
     * @param string $content
     * @param int    $batch
     */
    public function __construct(string $content, string $version, int $batch = 2)
    {
        parent::__construct($content, $version);
        $this->batch = $batch;
    }

    /**
     * @return int
     */
    public function getBatch() : int
    {
        return $this->batch;
    }

    public function getContent() : string
    {
        return 'try { ' . parent::getContent() . ' } catch (e) { console.log(e); }';
    }
}
