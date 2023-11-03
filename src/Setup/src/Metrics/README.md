# Little Framework for Metrics

To implement the `status` command of the setup, this little framework for metrics
was introduced. This might evolve to some stand alone library within the ILIAS core
sometime and become the base for more elaborate monitoring requirements or
telemetrics.

## Metrics

The metrics are designed to be a common format for output of values to other
systems such as time series databases. Also, the surrounding facilities are meant
to be expanded some time in the future. This is designed as a data sink and not meant
to be used in internal processing in ILIAS or as a source for data. Hence no
connection to the `Refinery` (so far).

The central class in the framework is the [`Metric`](./Metric.php), which is
understood to be some quantity that can be measured about the system. It has
one of five predefined (and not expandable) types or is a key/value collection
of some other metrics. The decision to design this as a closed type is explained
below.

A metric can have three different stabilities (config, stable, volatile) which are
meant to make transparent how and when we expect changes in that metric to happen.
This could be used in the future to, e.g., improve the `status` command, so that
only volatile metrics can be queried frequently. A collection of metrics can be
of mixed stability.

Then, of course, a metric has a value according to its type and an optional (but
highly recommended!) description that should explain what it is that is measured.

## Storage

A storage is the actual sink for the metrics. It has one cental `store` method
and a bulk of convenience methods for quicker use. These could be moved to some
factory or builder for metrics some time in the future, but this seems to be
overkill atm. In general, since a `Metric` is designed as a closed sum type, we
do not expect it to be necessary that factories for `Metric`s need to be abstracted,
creation via `new` seems to be enough.

Currently there is one simple and naive implementation for a storage as a nested
array, and a wrapper `StorageOnPath` to modify the key that is used to store
metrics in an underlying storage. In the future, we expect other implementations
to arise, e.g. over the ILIAS DB or some telemetry system.

## Design Considerations

The `Metric` is designed as a closed sum type. `Closed` here means, that the type
is not open for new members, as we would expect in an OOP system. `Sum` means,
that there are certain variations in the type (the various value types for the
metric and the stabilities). This is implemented like this to make it possible
that consumers of metrics know every possible type of metric instead of only
accessing it via an interface.

We do not expect the types of metrics need to expand dynamically according to
the scenario. There will be various producers inside the system that can stick
to the formats of `Metric`s that are provided here. What will expand, maybe
even dynamically, are the `Storage`s where the metrics will be send. These would
be impossible to build with an open type without a defined interface. Designing
an interface would be prudent if we would expect dynamicism on both sides of
the problems, metrics and storage. Designing an interface, though, is hard and
seems to be overkill here, at least at the moment.
