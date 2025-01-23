<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Section;

/**
 * ---
 * description: >
 *   This example is not really about sections, but rather a demonstration
 *   of il.UI.Input.Container.
 *   Container will build up and access a hierarchical structure of form nodes in JS.
 *
 * expected output: >
 *   There are several sections with a variety of fields.
 *   Underneath the form are three buttons:
 *   - "log struct" will output a JS object in the browser's console that
 *     holds all relevant formnodes, i.e., unchecked optional children will be left out.
 *   - "fold" will collapse the form in an indented list of field labels and values
 *   - "unfold" will show the form again.
 * ---
 */
function foldable()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $number_input = $ui->input()->field()->numeric("number", "Put in a number.")
        ->withLabel("a number");
    $text_input = $ui->input()->field()->text("text", "Put in some text.")
        ->withLabel("a text");

    $file_input = $ui->input()->field()->file(
        new \ilUIDemoFileUploadHandlerGUI(),
        "Upload File",
        "you can drop your files here"
    );

    $link_input = $ui->input()->field()->link(
        "a LinkField",
        "enter label and url"
    );


    $section1 = $ui->input()->field()->section(
        [
            $number_input->withValue(5),
            $text_input->withValue('some text'),
            $file_input,
            $link_input,
        ],
        "first section",
        "fill in some values"
    );

    $optional_group = $ui->input()->field()->optionalGroup(
        [
            $ui->input()->field()->duration("a dependent duration field", ""),
            $ui->input()->field()->text("a dependent text field", "")
        ],
        'optional group',
        'check to edit the field of the group',
    );
    $optional_group2 = $ui->input()->field()->optionalGroup(
        [
        $ui->input()->field()->section(
            [
                $ui->input()->field()->tag(
                    "Basic Tag",
                    ['Interesting', 'Boring', 'Animating', 'Repetitious'],
                    "Just some tags"
                ),
                $rating = $ui->input()->field()->rating("Rate with the Stars:", "change the rating")
            ],
            'fields in opt. section'
        )],
        'optional section',
        'byline opt. section',
    );

    $group1 = $ui->input()->field()->group(
        [
            "field_1_1" => $ui->input()->field()->text("Item 1.1", "Just some field"),
            "field_1_2" => $ui->input()->field()->text("Item 1.2", "Just some other field"),
        ],
        "Switchable Group number one",
        "Byline for Switchable Group number one"
    );
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );
    $group2 = $ui->input()->field()->group(
        [
            $ui->input()->field()->multiselect("now, pick more", $options, "This is the byline of multi text")
                ->withValue([2,3]),
            $ui->input()->field()->select("now, select one more", $options, "This is the byline of select")
                ->withValue(2),

            $ui->input()->field()->radio("now, pick just one more", "byline for radio (pick one more)")
                ->withOption('single1', 'Single 1')
                ->withOption('single2', 'Single 2')
        ],
        "Switchable Group number two",
        "Byline for Switchable Group number two"
    );
    $switchable_group = $ui->input()->field()->switchableGroup(
        [
            "g1" => $group1,
            "g2" => $group2,
        ],
        "Pick One",
        "Byline for the whole Switchable Group (pick one)"
    );

    $section2 = $ui->input()->field()->section(
        [   $number_input->withValue(7),
            $text_input->withValue('some other text'),
            $optional_group->withValue(null),
            $switchable_group,
            $text_input->withValue('final words'),
        ],
        "second section",
        "fill in some other values"
    );


    $form = $ui->input()->container()->form()->standard('#', [$optional_group2, $section1, $section2]);

    $button_js = $ui->button()->standard('log struct', '')->withOnLoadCode(
        fn($id) => "document.querySelector('#{$id}').addEventListener(
            'click',
            (event) => console.log(
                il.UI.Input.Container.get(event.srcElement.parentNode.querySelector('form').id)
                .getNodes()
            )
        );"
    );
    $button_unfold = $ui->button()->standard('unfold', '')->withOnLoadCode(
        fn($id) => "document.querySelector('#{$id}').addEventListener(
            'click',
            (event) => {
                const form = event.srcElement.parentNode.querySelector('form');
                const nodes =  il.UI.Input.Container.get(form.id);
                const formparts = nodes.getNodes().getChildren();

                formparts.forEach((part) => {
                    const partArea = form.querySelector('[data-il-ui-input-name=\"' + part.getFullName() +'\"]');
                    const fieldArea = partArea.querySelector(':scope > div.c-input__field');
                    const valArea = partArea.querySelector(':scope > div.c-input__value_representation');

                    fieldArea.style.display = 'block';
                    valArea.innerHTML = '';
                });
            }
        );"
    );

    $button_fold = $ui->button()->standard('fold', '')->withOnLoadCode(
        fn($id) => "document.querySelector('#{$id}').addEventListener(
            'click',
            (event) => {
                const form = event.srcElement.parentNode.querySelector('form');
                const nodes =  il.UI.Input.Container.get(form.id);
                const formparts = nodes.getNodes().getChildren();

                formparts.forEach((part) => {
                    const values = nodes.getValuesRepresentation(part);
                    let txt = '';
                    values.forEach((v) => {
                        const {label, value, indent, type} = v;
                        txt = txt 
                            + '&nbsp;&nbsp;'.repeat(indent + 1)
                            + label 
                            + ' <small>(' + type.replace('-field-input', '') + ')</small> '
                            + ': <b>' + value + '</b>'
                            + '<br/>';
                    });
                   
                    const partArea = form.querySelector('[data-il-ui-input-name=\"' + part.getFullName() +'\"]');
                    const fieldArea = partArea.querySelector(':scope > div.c-input__field');
                    const valArea = partArea.querySelector(':scope > div.c-input__value_representation');
                    
                    fieldArea.style.display = 'none';
                    valArea.style.fontSize = '80%';
                    valArea.innerHTML = txt;
                });

            }
        );"
    );

    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData()[0];
    } else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render([
            $form,
            $button_js,
            $button_fold,
            $button_unfold
        ]);
}
