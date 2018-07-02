<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use CaT\Ente\Component;

/**
 * This is one step in the superior booking process of the user. It is provided as
 * an ente-component, since there will be multiple plugins participating
 * in the booking process. The order of the steps is determined via a priority.
 * Every step shows a form to the user and prompts the user for input. Once
 * the step is satisfied, the input of the user will be turned into a
 * serialisable form. This is then stored by the handler of this component
 * until all steps are finished. The step may show a short information for one
 * last confirmation based on the stored input. Afterwards the step needs
 * to process the stored input.
 */
interface SuperiorBookingStep extends Component, Step {

}