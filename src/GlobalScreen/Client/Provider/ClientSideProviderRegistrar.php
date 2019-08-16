<?php namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/**
 * Class ClientSideProviderRegistrar
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ClientSideProviderRegistrar
{

    /**
     * @var MetaContent
     */
    private $meta_content;


    /**
     * ClientSideProviderRegistrar constructor.
     *
     * @param MetaContent $meta_content
     */
    public function __construct(MetaContent $meta_content)
    {
        $this->meta_content = $meta_content;
        $this->meta_content->addJs('./src/GlobalScreen/Client/dist/GS.js', 1, 1);
        $this->meta_content->addJs('./src/GlobalScreen/Client/dist/test.js', 1, 2);
    }


    /**
     * @return string
     */
    public function registerProviderClientSideCode(ClientSideProvider $provider)
    {

        $provider_name = $provider->getClientSideProviderName();

        $deo = <<<EOT
        $( document ).ready(function() {
            var $provider_name;
            $provider_name = il.GS.Services.provider().getByProviderName('$provider_name');
            console.log($provider_name);
        });
EOT;

        $this->meta_content->addOnloadCode($deo);
    }
}
