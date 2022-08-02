# Booking Manager Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Booking Manager component employs the following services, please consult the respective privacy.mds
    - The Booking Manager integrates the Advanced **Metadata** service. This custom metadata do contain user-created metadata sets which may contain personal data depending on configuration.
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)

## Configuration

**Global**

  - The booking manager does not provide any global configuration.

**Booking Manager Repository Object**

  - **Public Reservations**: This setting controls, if all users having read permission can see other users reservations or not.
  - **Type "Fixed Schedule" > Reminder**: This settings controls, if notification e-mails (including account and reservation data) are being sent. Users with write permission will get mails with all reservations included. Users with read permission will only get notifications on their own reservations.

## Data being stored

**Participants**
- A user having write permission can assign users as participants to a booking manager repository object. This stores the **user id** of both, the **participant** and the **user who assigned** the participant. This mechanism is needed, to allow users having write permission to book objects for other users.

**Booking Preferences**
- In the manager mode "No Schedule, Using Preferences" users can select a number of objects as their preferences (e.g. topics). This stores the **user id** and the **booking object id** together to represent the users preference.

**Reservations**
- If an object is booked, the **user id** if the booker is stored together with the **booking object id** and the **time frame** (from-to) the object is being booked.
- If the object is used for another user, the **user id** of the assigning user is als stored.

## Data being presented

**Learner Presentation** (Read Permission)
- Own **reservations** and **preferences**
- If activated (see configuration), **reservations** of other users.

**Tutor Presentation** (Write Permission)
- All **reservations** and **preferences**
- All **participants** of the booking manager repository object, incl. first and lastname.

## Data being deleted

- If a reservation is **deleted**
  - the reservation data is deleted

- If repository **object is being deleted**
  - all reservation data is deleted
  - all preference data is deleted


## Data being exported

- XML Exports of the Booking Manager do not contain any personal data.