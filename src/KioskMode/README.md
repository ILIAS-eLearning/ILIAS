# Kiosk-Mode

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Purpose](#purpose)
* [Architecture](#architecture)
* [Implementing a Provider](#implementing-a-provider)
* [Implementing a Player](#implementing-a-player)

## Purpose

A kiosk mode is understood to be a special mode to display certain applications, or
in the context of ILIAS, certain views in an application where the user gets less
control over the application or feature than usual. This is traditionally used for
the unsupervised operation of the application at public locations like museums or
train stations, where the user e.g. should not be able to perform otherwise common
operations like closing a window or shutting down the system.

Similar requirements arise in ILIAS for different objects and different scenarios:

* A Test needs to be displayed in a restricted way for the use in an E-Exam.
* The LTI-Interface needs the ability to display objects in a Kiosk-Mode when it
  ILIAS is used as a Tool-Provider.
* The Learning Sequence requires a kiosk mode to maintain control over the general
  display and navigation.

This library facilitates the implementation of objects that can be viewed in a kiosk
mode and of features that display these objects in a kiosk mode. The provided means
aim to make it possible for objects to implement a kiosk mode only once and then be
used by all features that need to display objects in that kiosk mode. On the other
hand, features that require viewing other objects in a kiosk mode should be able to
use all objects that implement a kiosk mode.

This library does neither implement the kiosk-mode for certain objects, nor implements
a view on these objects. It rather provides interfaces that describe how consumers
and providers of a kiosk mode should collaborate and functionality that helps to
implement both sides of the collaboration.

This library is complemented by the [Kiosk-Mode-Service](../../Services/KioskMode/README.md)
that provides functionality to construct views for certain objects and concrete
database functionality.

## Architecture

A **provider** of a kiosk mode is an ILIAS-object that may be viewed in in a kiosk
mode. A **player** is any feature of ILIAS that wants to display ILIAS-objects in
the reduced way defined by the kiosk mode.

The **player** may request a **view** for a certain **provider** at the kiosk mode
service. The **view** then is the exclusive way for the **player** and the
**provider** to communicate.

The **view** implements the following functionality to display and control the
providing object via the kiosk mode:

* It provides **controls** to the player that can be used to modify the state of
  the view.
* It provides a function that **updates** a **state** based on the use of **controls**
  to track the condition of the view.
* It provides a function to **render** the **state** to allow the player to display
  the object in a kiosk mode.

The **player** in turn uses that functions of the view to display and execute the
kiosk mode of the object:

* It asks the view for **controls** and displays them in an appropriate way.
* It processes user input for the **controls** and asks the view to **update** the
  **state**. It makes sure that the state is persisted appropriately.
* It asks the view to **render** the **state** and displays the result in an
  appropritate way and visual environment, most possibly in conjunction with the
  controls.


## Implementing a Provider

Please use `ilKioskModeView` in `Services\KioskMode\classes\` as a base to implement
this for ILIAS-objects.

* `State` MUST only contain information about the view (not about LP, completion, ...)
* `View` MUST NOT maintain state of the view internally but put it into `State`.
* `View::render` MUST NOT render controls that trigger a page change. It MAY provide
  controls that open new browser windows.
* `View::render` MAY make asynchronues request and change the displayed HTML on the
  client-side accordingly.
* The button-controls provided in `View::buildControls` MUST only be used to change the
  views status. Changes in the systems status MUST be initiated via a POST-request.

## Implementing a Player

* The player SHOULD respect the order in which the view build controls.
* The player MUST respect the order in which the view builds locator entries and TOC
  entries.
* The player MUST use the commands and parameters provided by the view via
  `View::buildControls` when updating the View.
* The player MUST call `updateGet` on GET-Requests and `updatePost` on POST-requests.
* The player MUST ensure that a given `State` is unique per instance of View, i.e.
  keys and values MUST NOT leak to other instances of View.
