# Booking Manager

This documentation is about the main concepts and business rules. Technical documentation can be found in the separate [README-technical](./README-technical.md).

This section documents the general concepts and structures of the Booking Manager. 

## Overview

* A **booking pool** is a repository object that manages resources (booking objects) and their usage (reservations). There are two main types: Pools that are using schedules (e.g. for booking rooms) and pools without schedules (e.g. for booking term paper topics). Booking pools without schedule either allow direct booking, or assign objects by preferences.
* A pool can hold multiple **schedules**. Schedules contain a set of weekly time **slots** where bookings for objects can be made, e.g. "Monday 10:00-11:00".
* A pool manages multiple **booking objects** (resources), e.g. a room or a set of beamers. A booking object uses either no schedule (depending on the pool type) or exactly one schedule.
* Users can make **reservations** for booking objects on specific dates that correspond to a time slot of the schedule attached to the booking object.
* Users that make reservations in a pool are called **participants**. It is also possible to manually add participants to the pool, that did not make any reservations yet.

## Assignment by Preferences

- All participants have to select a fixed number (no more, no less) of preferences. This ensures equal probability of their choice for being selected.

**Assignment Procedure**

See [Feature Wiki](https://docu.ilias.de/goto_docu_wiki_wpage_5688_1357.html) for the general feature spec.

The assignments via preferences is done in two phases:

**Phase A**

* Calcualte the popularity p(t) of each topic (number of users u that have choosen a topic)
* Choose topic t with lowest p(t); where p(t) > 0 (most unpopular topic)
* Randomly choose user u who has t as preference
* remove user and topic from list, start from the beginning

**Phase B (only remaining users with no valid options)**

* Choose random remaining user u
* Calculate number of assignments for each topic a(t)
* Assign t with minimum a(t) to u
* remove user and topic from list, start from the beginning

## Notifications

Notifications are currently only sent as part of the reminder feature, if schedules are being used. The [feature wiki entry](https://docu.ilias.de/goto_docu_wiki_wpage_3240_1357.html) includes notifications for bookings/canceling bookings as well, however they have never been implemented.

- Users with "read" and without "write" permission will only be informed about their own upcoming bookings.
- Users with "write" permission be informed about all upcoming bookings.
- For reminders reservations are included for the whole time frame (current running time to of the cron job up to end of day x after current time). This will inform on some reservations multiple times (daily), see [Bug #26216](https://mantis.ilias.de/view.php?id=26216).

