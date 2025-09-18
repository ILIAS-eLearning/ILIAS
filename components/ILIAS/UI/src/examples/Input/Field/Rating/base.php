<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Rating;

use ILIAS\Data\FiveStarRatingScale;

/**
 * ---
 * description: >
 *   Base example showing how use a Rating Input.
 *
 * expected output: >
 *   ILIAS shows 4 Rating Inputs:
 *   1: > You may change the rating by clicking on a star (or "neutral")
 *   2: > You MUST change the rating, otherwise the form will display an error when submitted
 *   3: > disabled, You cannot change the rating
 *   4: > A Rating Input with a little line above.
 *
 *   When submitted, the selected values are displayed (in an array of Enums, with name and value).
 * ---
 */
function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $rating = $ui->input()->field()->rating("Rate with the Stars:", "change the rating")
        ->withValue(FiveStarRatingScale::AVERAGE);
    $rating_required = $ui->input()->field()->rating("Rate with the Stars:", 'this is required')
        ->withRequired(true);
    $rating_disabled = $ui->input()->field()->rating("Rate with the Stars:", "this is disabled")
        ->withValue(FiveStarRatingScale::BAD)
        ->withDisabled(true);
    $rating_average = $ui->input()->field()->rating("Follow the Stars:", "the little line above shows the current average")
        ->withValue(FiveStarRatingScale::TERRIBLE)
        ->withCurrentAverage(3.5);


    $form = $ui->input()->container()->form()
        ->standard('#', [
            'rating_1' => $rating,
            'rating_2' => $rating_required,
            'rating_3' => $rating_disabled,
            'rating_4' => $rating_average
        ]);

    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
