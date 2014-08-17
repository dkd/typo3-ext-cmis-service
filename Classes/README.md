TYPO3 CMIS: Developer Notes
===========================

## Basics

Initialization of the CMIS integration features happens through the root-level Initialization class. This class preconfigures factories and necessary environment settings and prepares features for use.

## Scopes

The main scopes of code contained in this namespace are:

### Analysis

The Analysis scope contains _code for analyzing records and tables of the current system_ in order to _detect which resources should be treated_ by this extension, as determined by the active configuration and/or automatic detection. The default implementation in a TYPO3 CMS context is based on TCA analysis and relies on a combination of automatically detected table columns and configuration of additional/custom columns (with custom configurations overriding any automatically detected configurations).

### Command

This tiny scope contains the TYPO3 CMS CLI integration, a basic CommandController which has the primary responsibility of processing the Task Queue via a Worker instance and the secondary responsibility of creating new Tasks to insert in the Queue (intended task types: maintenance, flushing, status readout, statistics etc).

The Command scope contains no business logic - this is separated into queueing and execution as documented in the following sections.

### Configuration

Inside this scope is contained a Manager as main API to read configured parameters. The Manager returns more specific Definitions of parameters for each scope of configuration - Network, Table etc. - and utilises a ConfigurationReader and ConfigurationWriter to read and write configuration. The reading and writing of configuration is programmed to an interface to make the implementation replaceable and a default set of implementations are provided which:

* Reads configuration from a YAML source
* Reads configuration from a TypoScript source
* Writes configuration to a YAML source

In a running TYPO3 CMS system the configuration is read from a YAML file if one exists, if one does not exists the configuration is read from TypoScript and immediately written as YAML which is then used on the next request. Upon clearing the cache in TYPO3 or making changes to a `sys_template` record, this YAML file is removed in order to trigger rewriting.

In essense, the YAML file is the _main storage of configuration_ but is implemented in such a way that this configuration can be written automatically as determined by detected and configured resources (see Analysis section).

### Execution

In this scope resides all the actual execution of commands - indexing of a record, extraction of text from a record's column, purging of unwanted CMIS records and similar. Rather than be contained in the Command scope the logic is placed here in order to decouple it from the framework. This Execution is wrapped inside a Task which can be processed by a Worker to generate a Result and/or a number of Errors.

Examples of responsibilities contained in this scope:

* Network operations to store and retrieve content from remote servers
* System commands to call external applications
* Operations which modify resources

State-modifying operations are contained in this scope rather than be spread around in other contexts for two main reasons: to provide a fixed API for executing said type of operation, and to increase test friendliness by way of an easily mockable operation and expected response to the operation.

### Factory

A standard set of Factory pattern implementations to consistently create instances of objects and assign any default attributes that may be defined as Configuration. Called as static, initialized by the special Initialization root-level class.

### Hook

A simple class to trigger operations based on record modification and cache flushing. Utilizes the Service to interact with CMIS and Stanbol as configured by the active Configuration Definition.

### Logging

Simple and replaceable implementation of a logger. The shipped implementation logs using the internal logging features of TYPO3 CMS.

### Queue

Basic implementation of a queue/worker pattern; simply stores Tasks which can be executed by Workers in order to generate a Result and/or a number of Errors. Shipped with a default implementation which stores in and processes Tasks from a database table, but made replaceable by other implementations to store tasks in third-party job queues.

Interaction with the Queue happens through the QueueManager API which queues the Tasks and assigns a Worker to a Task while maintaining the persisted queue state. Execution of Tasks happens through the Worker and the Queue simply manages the flow of Tasks.

### Service

Main Service-type APIs for interacting with the local system, CMIS and Stanbol at a consumer level. Contains only high-level functions and delegates to the Task scope as needed.

### Task

The Task scope contains an assortment of Tasks that can be performed by the system, each Task having a number of parameters. Instances are constructed and configured, then either queued (see Queue section) or immediately executed (see Execution scope). The behavior of this queue-or-execute approach can be configured per-Task as well as manually overridden in each Task.
