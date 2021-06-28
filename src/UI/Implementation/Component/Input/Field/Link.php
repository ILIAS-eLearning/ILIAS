<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery as Refinery;
use ILIAS\Data\URI;

/**
 * This implements the link input group.
 */
class Link extends Group implements C\Input\Field\Link
{
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        \ilLanguage $lng,
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

    protected function addValidation()
    {
        $txt_id = 'label_cannot_be_empty_if_url_is_set';
        $error = function (callable $txt, $value) use ($txt_id) {
            return $txt($txt_id, $value);
        };
        $is_ok = function ($v) {
            list($label, $url) = $v;
            $ok = (
                is_null($label)
                && is_null($url)
            ) || (
                !is_null($label) && !is_null($url)
                && strlen($label) > 0
                && is_a($url, URI::class)
            );
            return $ok;
        };

        $label_is_set_for_url = $this->refinery->custom()->constraint($is_ok, $error);
        $this->setAdditionalTransformation($label_is_set_for_url);
    }


    protected function addTransformation()
    {
        $trafo = $this->refinery->custom()->transformation(function ($v) {
            list($label, $url) = $v;
            return $this->data_factory->link($label, $url);
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
    protected function getConstraintForRequirement()
    {
        return null;
    }
}
