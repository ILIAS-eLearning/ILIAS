# VirusScanner Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The VirusScanner component scans files for viruses and malware during file upload processing in ILIAS. It acts as a pre-processor in the FileUpload pipeline, intercepting uploaded file streams before they are stored. Supported scanning backends are ClamAV, Sophos, AntiVir (deprecated), and ICAP-based remote scanners (via ICapRemote or ICapRemoteAvClient). The component is active only when a scanner is configured via `ilias.ini.php`; if no scanner is configured (`IL_VIRUS_SCANNER === 'None'` and no ICAP host is set), no scanning takes place. Configuration settings (scanner type, scan command, clean command, ICAP host/port/service/client path) are stored in `ilias.ini.php` under the `[tools]` section and contain no personal data.

## Integrated Components

The VirusScanner component does not integrate with any other ILIAS component that handles personal data. It uses the ILIAS logger infrastructure for writing scan result messages, but the logger itself is not a personal-data-bearing component.

## Data being stored

The VirusScanner component does not store any personal data. No database writes were found in the component's code. Scan and clean results are written only to the ILIAS system log (via `ilLogger::write()`). Log entries contain the scanner type, the original file name as supplied during upload, and the raw scanner output (e.g., `Virus Scanner (clamav) (File document.pdf): FILE INFECTED: [/tmp/...] (VIRUS: ...)`). The original file name is provided by the uploading user and may reflect user-chosen naming conventions, but no user identifiers (user ID, login, email) are recorded alongside it.

## Data being presented

The VirusScanner component does not present any personal data. Scan result messages returned to the caller (e.g., "File infected" or "File cleaned") reference only the uploaded file name and scanner output text; no personal identifiers are shown. These messages are surfaced to the person performing the upload by whichever ILIAS component triggered the upload, not by the VirusScanner component itself.

## Data being deleted

The VirusScanner component does not store any personal data and therefore has no deletion mechanism.

## Data being exported

The VirusScanner component does not store any personal data and therefore provides no export functionality.
