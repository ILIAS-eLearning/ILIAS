We intend to represent reports Abstractly by graphs.

A report consists of tables which will be represented by
nodes of a graph. Relations between tables, such as join-
conditions will be the edges of a graph. A table may contain
a number of columns, or fields.

The edges may be both, directed and undirected, representing
left/right-joins and inner-joins respectively. A node may only
have undirected and outgoing directed connections (mixed node), 
or incoming directed and outgoing directed connections (directed node).
This is due to non-commutativity of left/right-joins.

The task will be 
 1. to find a subraph, which is *relevant* for a
 given report configuration containing as little nodes as
 possible.
 2. to sort the relevant subgraph in a fashion that represents
 a proper join-order.

A relevant node/table contains fields, that are required 
inside the report, e.g. as value-provider or for filtering
purpose.

The relevant subgraph consists of all nodes traversed by non-
self-crossing paths that start and end at relevant nodes.

The sorting of some graph means to sort nodes according to
 1. their connections (wether a node is connected by means of only
 directed of only undirected edges.) Directed node > mixed node.
 2. their minimum distance from some initial node.

Once we have a relevant, sorted graph, it may be passed to a
graph-interpreter fetching the relevant data from a source and
processing it.
The source may be, for instance, a database (fetching and processing 
corresponds to building a query) or a collection of libraries
(representing tables).
Allthough all graph-interpreter operate on qualitatively equal graphs,
one needs distinct objects for distinct data sources.
