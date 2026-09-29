# Roadmap

## Short Term

### Remove `InitHttpServices` and `HeaderSettingsLegacyProxy`
The HTTP services are built by the bootstrap and exposed through `AllModernComponents`.
`Init\Dependencies\InitHttpServices`, together with `HeaderSettingsLegacyProxy` which reads the https
detection from `$DIC->iliasIni()`, is only used by tests (`InitHttpServicesTest`, `UI\Examples\ExamplesTest`,
`SystemStylesGlobalScreenToolProviderTest`) that need an http service in a hand-built container. Once those
tests use stubs of `GlobalHttpState`, both classes can be deleted.

## Mid Term

...

## Long Term

...
