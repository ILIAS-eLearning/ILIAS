<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Dialog;

use ILIAS\UI\Component\Modal\DialogContent;

use ILIAS\UI\URLBuilder;

function parameters()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //when expecting a response, we do not want to render other examples
    $example_namespace = ['dialog', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");


    //build a dialog
    $query_namespace = ['dialog', 'example1'];
    list($url_builder, $action_token, $amount_token) = $url_builder->acquireParameters($query_namespace, "action", "amount");
    $url_builder = $url_builder->withParameter($action_token, "showmodal");

    $default_uri = $url_builder->withParameter($amount_token, "1")->buildURI();
    $dialog = $factory->modal()->dialog($default_uri);

    //build some endpoint
    $query = $DIC->http()->wrapper()->query();
    $icon = $factory->symbol()->icon()->standard('someExample', 'Example');
    $item = $icon;
    $links = [];

    if($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string());
        $amount = $query->retrieve($amount_token->getName(), $refinery->kindlyTo()->int()) ;
        switch ($action) {
            case 'showmodal':
                foreach (range(1, $amount) as $idx) {
                    $link_uri = $url_builder
                        ->withParameter($action_token, "dialoglink")
                        ->withParameter($amount_token, (string)$idx)
                        ->buildURI()->__toString();
                    $links[] = $factory->link()->standard((string)$idx, $link_uri);
                }
                $buttons = [
                    $factory->button()->standard('OK', '#')->withOnLoadCode(
                        fn($id) => "$('#$id').on('click', (e)=> {alert('$id');});"
                    )
                ];
                $dialog_content = $factory->messageBox()->info('some text')
                    ->withLinks($links)
                    ->withButtons($buttons);
                break;

            case 'dialoglink':
                $back_uri = $url_builder
                        ->withParameter($action_token, "showmodal")
                        ->withParameter($amount_token, (string)$amount)
                        ->buildURI()->__toString();
                $back = $factory->button()->standard('back', '#')->withOnLoadCode(
                    fn($id) => "$('#$id').on('click', (e)=> {
                        let dialogId = e.target.closest('dialog').parentNode.id;
                        il.UI.modal.dialog.get(dialogId).show('$back_uri');
                    });"
                );
                $dialog_content = $factory->messageBox()->info((string)$amount)
                    ->withButtons([$back]);
                break;

            default:
                throw new \Exception('?' . $action . $amount);
        }

        $response = $factory->modal()->dialogResponse($dialog_content);
        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Simple Dialog', $dialog->getShowSignal());
        $close_button = $factory->button()->standard('close Simple Dialog', $dialog->getCloseSignal());

        $uri = $url_builder->withParameter($amount_token, "8")->buildURI();
        $show_button8 = $factory->button()->standard('Show Dialog with Parameter', $dialog->getShowSignal($uri));

        $uri = $url_builder->withParameter($amount_token, "78")->buildURI();
        $show_button78 = $factory->button()->standard('Show Dialog with a lot of Items', $dialog->getShowSignal($uri));

        return $renderer->render([
            $dialog,
            $show_button, $show_button8, $show_button78
        ]);
    }
}
