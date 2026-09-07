# Environment

Read access to the two things an ILIAS installation runs on top of and does not decide at runtime: the
machine it is executed by, and the configuration it was installed with.

## Where does a new object go?

`src/Configuration/` holds three namespaces, separated by who determines the value:

| Namespace | Determined by | Holds |
|---|---|---|
| `Configuration\Server` | PHP and the host, ILIAS only reads it | PHP version and SAPI, error handling, memory and upload limits, execution constraints, OS |
| `Configuration\Installation` | this installation, written by the setup | `ilias.ini.php` and `client.ini.php` access, the client id of the current request, the working directories derived from both |
| `Configuration\Ini` | neither, it is file format machinery | reading and writing INI files, without any knowledge of what is configured in them |

So: a value PHP reports goes to `Server`, a value the setup wrote goes to `Installation`, and code that only
knows about sections and keys goes to `Ini`.

Everything in `Configuration\Ini` is `@internal` to this component. Consumers depend on the typed interfaces
`IliasIni`, `ClientIni`, `Directories`, `ClientIdProvider` and `ServerConfiguration`, never on a repository.

## Reading configuration

The component wires all five interfaces in `Environment.php`. Other components pull them through the
component bootstrap:

```php
$use[\ILIAS\Environment\Configuration\Installation\ClientIni::class]
```

Legacy code reaches `ilias.ini.php` and `client.ini.php` through the deprecated `\ilIniFile` wrappers in
`$DIC['ilIliasIniFile']` and `$DIC['ilClientIniFile']`, which are backed by the same repositories. New code
uses the typed interfaces.
