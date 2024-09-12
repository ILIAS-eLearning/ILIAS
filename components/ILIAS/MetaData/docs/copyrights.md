# Copyright Administration

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

ILIAS 10 comes pre-installed with seven Creative Commons Licences as well
as 'All rights reserved'. Copyright can be selected for objects that support
LOM when 'Enable Copyright Selection' is checked in the 'Copyright'-tab
of the Metadata Administration. The copyright of an object can be selected
along with and is persisted in its LOM, see the subtab 'LOM' of the
'Metadata'-tab in the object.

Installations which were not set up from scratch, but upgraded from a
lower ILIAS version, are not retroactively equipped with the current
version of the CC licences. These can however be added manually.

The list of preset copyright licences can be expanded as needed,
and existing licences edited or deleted (pre-installed or not).
Only the [default licence](#default-licence) can not be deleted.

## Copyright in LOM

If a preset copyright is chosen for an object, an identifier of
that copyright is written to `rights > description > string`.
That identifier does not convey any information to users on its own,
and should be translated into useful information using the
[LOM API](api.md).

When users enter custom copyright information for an object, it
is also written to`rights > description > string`. It is not
possible to select a preset copyright and also add additional
information.

## Pre-Installed Licenses

| Title                                                    | Description                                                                                          | Full Name                                                                          | URL                                               | Image URL                                                                   |
|----------------------------------------------------------|------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|---------------------------------------------------|-----------------------------------------------------------------------------|
| All rights reserved                                      | The copyright holder reserves, or holds for their own use, all the rights provided by copyright law. | All rights reserved                                                                | -                                                 | -                                                                           |
| Attribution Non-commercial No Derivatives (BY-NC-ND) 4.0 | Creative Commons License                                                                             | Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License | http://creativecommons.org/licenses/by-nc-nd/4.0/ | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-nd.svg |
| Attribution Non-commercial Share Alike (BY-NC-SA) 4.0    | Creative Commons License                                                                             | Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License    | http://creativecommons.org/licenses/by-nc-sa/4.0/ | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-sa.svg |
| Attribution Non-commercial (BY-NC) 4.0                   | Creative Commons License                                                                             | Creative Commons Attribution-NonCommercial 4.0 International License               | http://creativecommons.org/licenses/by-nc/4.0/    | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc.svg    |
| Attribution No Derivatives (BY-ND) 4.0                   | Creative Commons License                                                                             | Creative Commons Attribution-NoDerivatives 4.0 International License               | http://creativecommons.org/licenses/by-nd/4.0/    | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nd.svg    |
| Attribution Share Alike (BY-SA) 4.0                      | Creative Commons License                                                                             | Creative Commons Attribution-ShareAlike 4.0 International License                  | http://creativecommons.org/licenses/by-sa/4.0/    | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-sa.svg    |
| Attribution (BY) 4.0                                     | Creative Commons License                                                                             | Creative Commons Attribution 4.0 International License                             | http://creativecommons.org/licenses/by/4.0/       | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by.svg       |
| Public Domain                                            | Creative work to which no exclusive intellectual property rights apply.                              | Public Domain                                                                      | http://creativecommons.org/publicdomain/zero/1.0/ | https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/cc-zero.svg  |

## Default Licence

As long as copyright selection is active, every ILIAS object which supports
LOM is shown as being under one of the available licences. Objects for which
no copyright is explicitely chosen are shown as being under the default
licence. This licence is always listed first in the 'Available Copyrights' table in
the Metadata Administration and cannot be deleted, but it can be edited. If 
not configured otherwise, 'All rights reserved' is the default.

When no other image is added to the default licence, it will be
displayed along with an ©-icon.

## Outdated Licences

In the Metadata Administration, preset copyright licences can
be set to 'Outdated'. Outdated copyright licences can not be
selected for objects anymore, but objects under an outdated
licence are still under this licence, and treated as such.

## Export/Import

Custom copyright information is written into export files of
objects as is, and imported as such. When copyright selection
is active, and a preset copyright licence is selected, only the
link to the licence is written into export files. If no link is
set, its full name is used instead.

Objects that have no copyright info in their LOM at all are
exported with the default copyright licence.

On import, ILIAS tries to match copyright information to the
preset copyright licences on the installation as follows:

1. If the copyright information contains the links to one of
   the licences on the installation, that licence is selected for
   the imported object. Differences in scheme ('https://' vs.
   'http://') are disregarded.
2. Else, if the copyright information matches exactly the full
   name of one of the licences on the installatiom, that licence
   is selected for the imported object.
3. Else, the copyright information is used as is as custom
   copyright.

For best results, preset copyright licences should not have
duplicate links or full names. If copyright selection is not
activated, all copyright information is treated as custom, and
imported and exported as is.
