# Roadmap

## Revise deletion processes

The deletion processes are not properly implemented. They should listen to object deletion events.

## DI Integration

Integrate into DI as a service.

## Common action dispatcher

Clarify common action dispatcher relationship.

## Use repository pattern

The current ilRating class should be transformed to a repository like pattern, the use of static methods should be prevented.

## Transform widget to KS

The rating widget should make use of KS components. The stars may be implemented by Glyphs, the overlay should be migrated to a Popover.
