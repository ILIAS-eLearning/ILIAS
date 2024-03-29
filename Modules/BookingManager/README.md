# Booking Manager

Public service documentation can be found in the separate [README-Service](./README-Service.md).

This section documents the general concepts and structures of the Booking Manager. 

* [Overview](#overview)
* [Booking Pool](#booking-pool)
* [Schedules](#schedules)
* [Booking Objects](#booking-objects)
* [Reservations](#reservations)
* [Participants](#participants)
* [Assignments by Preference](#assignment-by-preferences)
* [Notifications](#notifications)

## Overview

* A **booking pool** is a repository object that manages resources (booking objects) and their usage (reservations). There are two main types: Pools that are using schedules (e.g. for booking rooms) and pools without schedules (e.g. for booking term paper topics).
* A pool can hold multiple **schedules**. Schedules contain a set of weekly time **slots** where bookings for objects can be made, e.g. "Monday 10:00-11:00".
* A pool manages multiple **booking objects** (resources), e.g. a room or a set of beamers. A booking object uses either no schedule (depending on the pool type) or exactly one schedule.
* Users can make **reservations** for booking objects on specific dates that correspond to a time slot of the schedule attached to the booking object.
* Users that make reservations in a pool are called **participants**. It is also possible to manually add participants to the pool, that did not make any reservations yet.

## Booking Pool

A booking pool is the main entity for managing booking objects (resources) and their usage (reservations).

* **Code**: `Modules/BookingManager`
* **DB Tables**: `booking_settings`

### Properties

* **Fixed Schedule** or **No Schedule**: There are two main types of booking pools, those which are using schedules (e.g. for booking rooms) and those who don't (e.g. for selection of term paper topics). (`booking_settings.schedule_type`)
* **Public Reservations**: The list of reservations can be made publicly available for all users with read permission. (`booking_settings.public_log`)
* **Overall Limit of Bookings** (No schedule only): Limits the maximum number of bookings a single user can do in this pool. (`booking_settings.ovlimit`)
* **Default Period for Reservation List** (Fixed schedule only): Sets the default period of the filter in the reservation list view. (`booking_settings.rsv_filter_period`)
* **Reminder**: A reminder can be activated (`booking_settings.reminder_status`) to remind users of their upcoming bookings. The period before users are reminded can be set (`booking_settings.reminder_day`). A cronjob stores the timestamp for last execution (`booking_settings.last_remind_ts`).

*Deprecated*

* `booking_settings.slots_no` ?

## Schedules

* **Code**: `Modules/BookingManager/Schedule`
* **DB Tables**: `booking_schedule`, `booking_schedule_slot`

### Properties

...

## Booking Objects

* **Code**: `Modules/BookingManager/Objects`
* **DB Tables**: `booking_objects`

### Properties

...

## Reservations

* **Code**: `Modules/BookingManager/Reservations`
* **DB Tables**: `booking_reservation`

### Properties

* **User**: User who 'owns' the reservation. (`booking_reservation.user`)
* **Assigner**: User who created the reservation. A tutor (assigner) may assign a reservation to a student (user who owns the reservation). (`booking_reservation.assigner_id`)
* **Object**: Object that is reserved. (`booking_reservation.object_id`) 
* **Reservation Period**: Timestamps that correspond to a concrete instance of a slot of the object schedule. (`booking_reservation.date_from`, `booking_reservation.date_to`)
* **Reservation Status**: Currently `NULL` (reserved) or `ilBookingReservation::STATUS_CANCELLED` (reservation cancelled). (`booking_reservation.status`)
* **Reservation Grouping**: If multiple instances of an object are reserved, each of them will get an entry in the reservation table. They will all share the same internal group ID. (`booking_reservation.group_id`)
* **Context Object**: If a reservation is done within another repository object (e.g. course), this is stored with the reservation. (`booking_reservation.context_obj_id`)

## Participants

* **Code**: `Modules/BookingManager/Participants`
* **DB Tables**: `booking_member`

### Properties

...

## Assignment by Preferences

### Business Rules

**Procedure**

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

### Business Rules

- Users with "read" and without "write" permission will only be informed about their own upcoming bookings.
- Users with "write" permission be informed about all upcoming bookings.
- For reminders reservations are included for the whole time frame (current running time to of the cron job up to end of day x after current time). This will inform on some reservations multiple times (daily), see [Bug #26216](https://mantis.ilias.de/view.php?id=26216).

