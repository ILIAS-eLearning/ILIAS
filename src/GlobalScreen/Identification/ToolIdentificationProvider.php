<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class CoreIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolIdentificationProvider extends CoreIdentificationProvider implements ToolIdentificationProviderInterface
{

    /**
     * @inheritDoc
     */
    public function contextAwareIdentifier(string $identifier_string, bool $ignore_context = false) : IdentificationInterface
    {
        if ($ignore_context) {
            return parent::identifier($identifier_string);
        }
        global $DIC;

        $get = $DIC->http()->request()->getQueryParams();
        if (isset($get['ref_id'])) {
            $identifier_string .= '_' . $get['ref_id'];
        }

        return parent::identifier($identifier_string);
    }


    /**
     * @inheritDoc
     */
    public function identifier(string $identifier_string) : IdentificationInterface
    {
        throw new \LogicException('Tools must use contextAwareIdentifier');
    }
}
