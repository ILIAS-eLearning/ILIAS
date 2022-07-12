<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\DateTime;

/**
 * Base example showing how to plug date-inputs into a form
 */
function base()
{

    //Step 0: Declare dependencies
    global $DIC;

    $ui = $DIC->ui()->factory();
    $data = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $ctrl = $DIC->ctrl();

    //Step 1: define the inputs
    $date = $ui->input()->field()->dateTime("Pick a date/time", "Pick any date you want. It will be shown in format YYYY-MM-DD");

    $formatted = $date
        ->withMinValue(new \DateTimeImmutable())
        ->withFormat($data->dateFormat()->germanShort())
        ->withLabel('future only')
        ->withByline('Only allows to pick a date in the future. It will be shown in format DD.MM.YYYY');

    $time = $date->withTimeOnly(true)
        ->withLabel('time only')
        ->withByline('Only pick a time. It will be shown in format HH:mm');

    $both = $date->withUseTime(true)
        ->withLabel('both date and time')
        ->withByline('Pick any date and time you want. It will be shown in format YYYY-MM-DD HH:mm and be saved for your local time zone.');

    //setting a timezone will return a date with this timezone.
    $tz = 'Asia/Tokyo';
    $timezoned = $both->withTimezone($tz)
        ->withValue('')
        ->withLabel('to Tokyo time')
        ->withByline('Pick any date and time you want. It will be shown in format YYYY-MM-DD HH:mm and be saved for Tokyo time zone.');

    //if you want a date converted to the timezone, do it on the date:
    $date_now = new \DateTime('now');
    $date_zoned = new \DateTime('now', new \DateTimeZone($tz));

    //here is the usage of Data/DateFormat
    $format = $timezoned->getFormat()->toString() . ' H:i';
    $timezoned_preset1 = $timezoned
        ->withValue($date_now->format($format))
        ->withLabel('to Tokyo time with local preset')
        ->withByline('Local time+date is preset. However, output will be in Tokyo timezone');
    $timezoned_preset2 = $timezoned
        ->withValue($date_zoned->format($format))
        ->withLabel('Tokyo time, both preset and output')
        ->withByline('Tokyo time+date is preset. Output is also Tokyo time.');

    $disabled = $date
        ->withValue($date_now->format($format))
        ->withDisabled(true)
        ->withLabel('disabled')
        ->withByline('You cannot pick anything, as the field is disabled');

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [
        'date' => $date,
        'formatted' => $formatted,
        'time_only' => $time,
        'both_datetime' => $both,
        'to_tokyotime' => $timezoned,
        'tokyotime_local_preset' => $timezoned_preset1,
        'tokyotime' => $timezoned_preset2,
        'disabled' => $disabled
    ]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
