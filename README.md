# :point_right: Nudge
> The Nudge plugin for MOODLE and Totara aims to provide a simple way to notify/remind users to revisit their course prior to course completion.
>> The pugin supports multiple translation, templated messages, recurring remind dates, relative remind dates and the option to remind managers in both Totara *and MOODLE*.

| Table of contents                     |
| :------------------------------------ |
| [What this is not](#what-this-is-not) |
| [Contributing](#contributing)         |
| [TODOs](#todos)                       |
| [IDEAs](#ideas)                       |
| [Versioning](#versioning)             |
| [Installation](#installation)         |
| [Credits](#credits)                   |


## Documentation
> Proper documentation is provided in the [Docs Folder](./docs/) beginning at [Documentation](./docs/index.md).

## What this is not
 - A magic wand that induces instant compliance
 - A activity completion reminding system for that we'd recommend the [Re-engagement Activity](https://github.com/catalyst/moodle-mod_reengagement).
 - A complex system, The entirety of the completion checks for this plugin reside in a single call to:
    ```php
    (new completion_info($course))->is_course_complete($userid)
    ```
 - *Fast* - This plugin is not designed to be fast and efficient its designed to be easy to work with and test.
    If you have issues with the processing time of the [Nudge Task](./classes/task/nudge_task.php), submit a feature flag gated PR or run it on a [worker](https://docs.moodle.org/311/en/Cron#Scaling_up_cron_with_multiple_processes).

## Contributing
Welcomed :-)

## TODOs
 1. Migrate ideas and TODOs to GH issues.
 1. TODO flesh out readme.
 1. Installation process written up.
 1. Initial user guide.
 1. Initial developer guide.

## IDEAs 
 1. IDEA: Benchmark phpunit testsuite to run a different test suite that tests operations in bulk numerous 1000s then reports on time.

## Versioning
| PHP Version |       LMS Version        |             Nudge Branch             |
| :---------: | :----------------------: | :----------------------------------: |
|             |          MOODLE          |                                      |
|    `7.4`    | **<** `MOODLE_39_STABLE` |              Not tested              |
|    `7.4`    |    `MOODLE_39_STABLE`    | [main](/github/sproutlabs/tree/main) |
|    `7.4`    |   `MOODLE_310_STABLE`    | [main](/github/sproutlabs/tree/main) |
|    `7.4`    |   `MOODLE_311_STABLE`    | [main](/github/sproutlabs/tree/main) |
|    `7.4`    |    `MOODLE_40_STABLE`    |              Not tested              |
|    `7.4`    | **>** `MOODLE_40_STABLE` |              Not tested              |
|             |          Totara          |                                      |
|    `7.4`    |    **<** `Totara 13`     |              Not tested              |
|    `7.4`    |       `Totara 13`        | [main](/github/sproutlabs/tree/main) |
|    `7.4`    |       `Totara 14`        |              Not tested              |
|    `7.4`    |       `Totara 15`        | [main](/github/sproutlabs/tree/main) |
|    `7.4`    |    **>** `Totara 15`     |              Not tested              |
## Installation
 TODO - Install Instructions.
 1. :warning: **MUST** review and follow [File Security](./docs/dev/05-file-security.md) for a secure install.

## Credits
 - [Bradken](https://bradken.com/) for sponsoring the development of this plugin and allowing us to share it with the community.
 - [Catalyst IT](https://github.com/catalyst/) for sharing their plugins publicly as great architecture references and learning material.
