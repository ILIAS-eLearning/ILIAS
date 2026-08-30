# Verification Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Verification component provides the base infrastructure for certificate verification objects in
ILIAS. A verification object represents a snapshot of a user's certificate, stored as a PDF file in
the user's personal workspace. Verification objects are created when a user requests a verification
copy of a certificate they have earned (e.g., for a course, test, exercise, SCORM module, CMI5/xAPI
activity, or LTI consumer).

The Verification component itself provides the abstract base class (`ilVerificationObject`) and the
certificate-based file service (`ilCertificateVerificationFileService`). The concrete verification
types (course verification, test verification, etc.) are implemented in their respective components
(Course, Test, Exercise, ScormAicc, CmiXapi, LTIConsumer), which extend the base class. The data
stored and deleted by the Verification component is described here; the creation workflow and GUI
presentation are handled by the consuming components.

Verification objects are personal workspace objects. They are owned by the user who created them and
are not placed in the repository. Access to verification objects in the workspace is controlled by
the WorkspaceFolder component's access handler.

## Integrated Components

- The Verification component employs the following components, please consult the respective
  PRIVACY.md files:
    - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md) — the Verification component uses the Certificate
      component to generate PDF certificate files via `ilUserCertificateRepository` and
      `ilCertificatePdfAction`.
    - ILIASObject — the Verification component extends `ilObject2`,
      which stores object metadata (owner user ID, creation date, last update) in the `object_data`
      table.
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md) — the `ilPCVerification` page content element allows embedding
      verification objects in portfolio pages. The page content stores the verification type, object
      ID, and user ID as XML attributes.
    - [Portfolio](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Portfolio/PRIVACY.md) — verification objects can be embedded in portfolio pages
      for presentation to others. Access checks and PDF downloads from portfolio pages are handled
      via `ilPCVerification::isInPortfolioPage()`.
    - [WorkspaceFolder](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/WorkspaceFolder/PRIVACY.md) — verification objects are stored in the
      user's personal workspace. Access control for verification objects is managed by the workspace
      access handler.

## Data being stored

- **Issued-on date**: The **date** when the original certificate was acquired is stored in the
  `il_verification` table (column `parameters`, with `type` = `issued_on`). This records when the
  user completed the underlying learning activity.
- **Certificate PDF filename**: The **filename** of the generated certificate PDF is stored in the
  `il_verification` table (column `parameters`, with `type` = `file`). The filename follows the
  pattern `{objectType}_{objectId}_{userId}.pdf`, which embeds the **user ID** of the certificate
  holder directly in the filename.
- **Certificate PDF file**: A **PDF file** containing the user's certificate is stored on the
  filesystem via `ilVerificationStorageFile` at the path `ilVerification/vrfc/{id}/certificate/`.
  The PDF contains personal data as defined by the certificate template (typically the user's name,
  the completed activity, and the date of completion).
- **Object owner**: Via the `ilObject2` base class, the **user ID** of the account that created the
  verification object is stored as the owner in the `object_data` table, along with the **creation
  date** and **last update timestamp**.

## Data being presented

- **Each user** can view and download their own verification objects (certificate PDFs) in their
  personal workspace.
- **Each user** can embed their verification objects in portfolio pages via the `ilPCVerification`
  page content element. When embedded, the verification object's title and a download link are
  rendered.
- When a verification object is embedded in a **shared portfolio page**, any person with access to
  that portfolio page can view the verification title and download the certificate PDF. Portfolio
  sharing is controlled by the Portfolio component.
- Access to verification objects in the personal workspace is controlled by the workspace access
  handler. **Persons granted "Read" access** to a workspace node can view and download the
  verification object's certificate PDF.

## Data being deleted

- **When a verification object is deleted** by its owner from the personal workspace:
    - the certificate PDF file is removed from the filesystem via `ilVerificationStorageFile::delete()`.
    - all property records for the verification object are deleted from the `il_verification` table.
    - the object entry is removed from `object_data` by the `ilObject2` base class.
- **When a user account is deleted**:
    - the user's personal workspace, including all verification objects, is deleted. This removes
      the `il_verification` records, the filesystem storage, and the `object_data` entries for all
      verification objects owned by that user.
    - if the user's verification objects were embedded in portfolio pages, the page content references
      (`ilPCVerification` XML) may persist until the portfolio is also deleted, but the verification
      object itself (and its PDF) will no longer be accessible.

## Data being exported

- The Verification component does not provide a dedicated export feature for verification objects.
- The certificate PDF itself can be downloaded by the owning user, which effectively serves as a
  manual export of the certificate data.
- When a verification object is embedded in a portfolio page, the certificate PDF can be downloaded
  by persons with access to that portfolio page.
