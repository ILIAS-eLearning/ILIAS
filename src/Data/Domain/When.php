<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * Unfortunately we don't have method overloading in PHP. By implementing the `When` trait, you can simulate that by
 * delegating `when($myEvent)` to `whenMyEvent($myEvent)`. This prevents us from having to use conditionals to determine
 * how to react to an event.
 */
trait When {

	abstract protected function when(DomainEvent $event);
} 