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

namespace ILIAS\LegalDocuments;

use Closure;
use ILIAS\LegalDocuments\Provide\ProvidePublicPage;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Provide\ProvideWithdrawal;

class LazyProvide extends Provide
{
    /** @var Closure(): Provide */
    private Closure $provide;

    /**
     * @param Closure(): Provide $create
     */
    public function __construct(Closure $create)
    {
        $this->provide = function () use ($create) {
            $provide = $create();
            $this->provide = fn() => $provide;
            return $provide;
        };
    }

    public function withdrawal(): ProvideWithdrawal
    {
        return ($this->provide)()->withdrawal();
    }

    public function publicPage(): ProvidePublicPage
    {
        return ($this->provide)()->publicPage();
    }

    public function document(): ProvideDocument
    {
        return ($this->provide)()->document();
    }

    public function history(): ProvideHistory
    {
        return ($this->provide)()->history();
    }

    public function allowEditing(): Provide
    {
        return ($this->provide)()->allowEditing();
    }
}
