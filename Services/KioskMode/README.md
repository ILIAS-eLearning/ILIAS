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

This provides implementations for the [Kiosk-Mode](../../src/KioskMode/README.md) to
construct views for certain objects and persist state in the database.

## Public Interface

* `ilKioskModeService` is the central entry  point for consumers of the service.
* `ilKioskModeView` MUST be implemented by modules that want to provide a kiosk
  mode. Implementation MUST be located in the `classes` folder of their respective
  Module where the implementation is named il$MODULEKioskModeView. Implementations
  must adhere to the interface `ILIAS\KioskMode\View`.
