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
 
namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\Refinery\Constraint;
use ilLanguage;

/**
 * This implements the link input group.
 */
class Link extends Group implements C\Input\Field\Link
{
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng,
        Factory $field_factory,
        string $label,
        string $byline
    ) {
        $inputs = [
            $field_factory->text($lng->txt('ui_link_label')),
            $field_factory->url($lng->txt('ui_link_url'))
        ];

        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);
        $this->addValidation();
        $this->addTransformation();
    }

    protected function addValidation() : void
    {
        $txt_id = 'label_cannot_be_empty_if_url_is_set';
        $error = fn (callable $txt, $value) => $txt($txt_id, $value);
        $is_ok = function ($v) {
            list($label, $url) = $v;
            return (
                (is_null($label) || $label === '') &&
                is_null($url)
            ) || (
                !is_null($label) && !is_null($url)
                && strlen($label) > 0
                && is_a($url, URI::class)
            );
        };

        $label_is_set_for_url = $this->refinery->custom()->constraint($is_ok, $error);
        $this->setAdditionalTransformation($label_is_set_for_url);
    }


    protected function addTransformation() : void
    {
        $trafo = $this->refinery->custom()->transformation(function ($v) : ?\ILIAS\Data\Link {
            list($label, $url) = $v;
            if (is_null($url) || $url === "") {
                return null;
            }
            return $this->data_factory->link($label ?? "", $url);
        });

        $this->setAdditionalTransformation($trafo);
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
    {
        return null;
    }
}
