# Roadmap

## Short Term

### Replace the scanner closure
`VirusScannerPreProcessor` receives a `\Closure` returning `?ilVirusScanner` instead of the scanner, because
`ilVirusScannerFactory::_getInstance()` depends on `IL_VIRUS_SCANNER`, a constant defined from `ilias.ini.php`
by `ilInitialisation` after the bootstrap was built. The component should read the scanner configuration from
`ILIAS\Environment\Configuration\Installation\IliasIni` and implement a `VirusScanner` interface of its own,
which the pre-processor then receives directly.

## Mid Term

...

## Long Term

...
