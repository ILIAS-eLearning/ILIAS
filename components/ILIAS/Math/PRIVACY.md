# Math Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Math component is a pure computational utility library that provides arbitrary-precision arithmetic operations for use by other ILIAS components. It exposes static methods for addition, subtraction, multiplication, division, power, square root, modulo, and greatest common divisor calculations, as well as expression evaluation via `EvalMath`. The component selects an appropriate adapter backend at runtime — either PHP's native BCMath extension (`ilMathBCMathAdapter`) or a fallback PHP implementation (`ilMathPhpAdapter`) — and normalises numbers across locales to ensure consistent results. The Math component contains no user-facing interface and processes only numeric values passed to it by calling components. It has no privacy-relevant behaviour of its own.

## Integrated Components

The Math component does not integrate any other ILIAS components that handle personal data. It is listed in `maintenance.json` with an empty `used_in_components` array and its `Math.php` entry point registers no service dependencies.

## Data being stored

The Math component does not store any personal data. No database queries, insertions, or updates of any kind are present in the component's source code.

## Data being presented

The Math component does not present any personal data. It returns computed numeric values to the calling component and has no UI layer of its own.

## Data being deleted

The Math component does not delete any personal data. Because no data is stored, there is nothing to delete on user action, account deletion, or any other trigger.

## Data being exported

The Math component does not export any personal data. It provides no export functionality.
