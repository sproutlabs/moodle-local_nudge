# File Security
> This table shows each file distributed via a git install of this plugin.

Because of MOODLE's architecture addtional security measures are required on install.

If a file has `REQUIRED` you **MUST** take action to prevent unwanted data leakage.

If a file has `TODO` or is not present on this list please raise a GH issue.

> Note that Nudge ships with some simple checks to help establish if *some* of these files are secure.
> You can access these at:
```php
$url = "{$CFG->wwwroot}/report/security/index.php";
```
or:
`https://subdomain.atdomain.tld/report/security/index.php`


| File                                                                                                                             | Security Measure | Action Required                                             |
| :------------------------------------------------------------------------------------------------------------------------------- | ---------------- | ----------------------------------------------------------- |
| [.github/workflows/moodle-ci.yml](../../.github/workflows/moodle-ci.yml)                                                         | REQUIRED         | Deny dotfiles                                               |
| [.gitignore](../../.gitignore)                                                                                                   | REQUIRED         | Deny dotfiles                                               |
| [.vscode/nudge.code-workspace](../../.vscode/nudge.code-workspace)                                                               | REQUIRED         | Deny dotfiles                                               |
| [LICENCE](../../LICENCE)                                                                                                         | PUBLIC           | None                                                        |
| [README.md](../../README.md)                                                                                                     | REQUIRED         | Deny all markdown files                                     |
| [classes/dml/abstract_nudge_db.php](../../classes/dml/abstract_nudge_db.php)                                                     | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/dml/nudge_db.php](../../classes/dml/nudge_db.php)                                                                       | REQUIRED         | Deny classes directory                                      |
| [classes/dml/nudge_notification_content_db.php](../../classes/dml/nudge_notification_content_db.php)                             | REQUIRED         | Deny classes directory                                      |
| [classes/dml/nudge_notification_db.php](../../classes/dml/nudge_notification_db.php)                                             | REQUIRED         | Deny classes directory                                      |
| [classes/dml/nudge_user_db.php](../../classes/dml/nudge_user_db.php)                                                             | REQUIRED         | Deny classes directory                                      |
| [classes/dto/nudge_notification_form_data.php](../../classes/dto/nudge_notification_form_data.php)                               | REQUIRED         | Deny classes directory                                      |
| [classes/event/nudge_created.php](../../classes/event/nudge_created.php)                                                         | REQUIRED         | Deny classes directory                                      |
| [classes/event/nudge_deleted.php](../../classes/event/nudge_deleted.php)                                                         | REQUIRED         | Deny classes directory                                      |
| [classes/event/nudge_updated.php](../../classes/event/nudge_updated.php)                                                         | REQUIRED         | Deny classes directory                                      |
| [classes/form/nudge/delete.php](../../classes/form/nudge/delete.php)                                                             | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/form/nudge/edit.php](../../classes/form/nudge/edit.php)                                                                 | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/form/nudge_notification/delete.php](../../classes/form/nudge_notification/delete.php)                                   | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/form/nudge_notification/edit.php](../../classes/form/nudge_notification/edit.php)                                       | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/local/abstract_nudge_entity.php](../../classes/local/abstract_nudge_entity.php)                                         | REQUIRED         | Deny classes directory                                      |
| [classes/local/nudge.php](../../classes/local/nudge.php)                                                                         | NONE             | Optional: Additionally covered by denying classes directory |
| [classes/local/nudge_notification.php](../../classes/local/nudge_notification.php)                                               | REQUIRED         | Deny classes directory                                      |
| [classes/local/nudge_notification_content.php](../../classes/local/nudge_notification_content.php)                               | REQUIRED         | Deny classes directory                                      |
| [classes/local/nudge_user.php](../../classes/local/nudge_user.php)                                                               | REQUIRED         | Deny classes directory                                      |
| [classes/task/nudge_task.php](../../classes/task/nudge_task.php)                                                                 | NONE             | Optional: Additionally covered by denying classes directory |
| [composer.json](../../composer.json)                                                                                             | REQUIRED         | Deny composer.json files                                    |
| [db/access.php](../../db/access.php)                                                                                             | NONE             | None                                                        |
| [db/install.xml](../../db/install.xml)                                                                                           | REQUIRED         | Deny install.xml files                                      |
| [db/messages.php](../../db/messages.php)                                                                                         | NONE             | None                                                        |
| [db/tasks.php](../../db/tasks.php)                                                                                               | NONE             | None                                                        |
| [delete_notification.php](../../delete_notification.php)                                                                         | PUBLIC           | None                                                        |
| [delete_nudge.php](../../delete_nudge.php)                                                                                       | PUBLIC           | None                                                        |
| [docs/dev/01-overview.md](../../docs/dev/01-overview.md)                                                                         | REQUIRED         | Deny all markdown files                                     |
| [docs/dev/02-entity.md](../../docs/dev/02-entity.md)                                                                             | REQUIRED         | Deny all markdown files                                     |
| [docs/dev/03-dml-db.md](../../docs/dev/03-dml-db.md)                                                                             | REQUIRED         | Deny all markdown files                                     |
| [docs/dev/04-task.md](../../docs/dev/04-task.md)                                                                                 | REQUIRED         | Deny all markdown files                                     |
| [docs/index.md](../../docs/index.md)                                                                                             | REQUIRED         | Deny all markdown files                                     |
| [docs/usr/01-overview.md](../../docs/usr/01-overview.md)                                                                         | REQUIRED         | Deny all markdown files                                     |
| [docs/usr/02-notifications.md](../../docs/usr/02-notifications.md)                                                               | REQUIRED         | Deny all markdown files                                     |
| [docs/usr/03-nudges-courses.md](../../docs/usr/03-nudges-courses.md)                                                             | REQUIRED         | Deny all markdown files                                     |
| [docs/usr/04-tips.md](../../docs/usr/04-tips.md)                                                                                 | REQUIRED         | Deny all markdown files                                     |
| [edit_notification.php](../../edit_notification.php)                                                                             | PUBLIC           | None                                                        |
| [edit_nudge.php](../../edit_nudge.php)                                                                                           | PUBLIC           | None                                                        |
| [lang/en/local_nudge.php](../../lang/en/local_nudge.php)                                                                         | NONE             | None                                                        |
| [lib.php](../../lib.php)                                                                                                         | NONE             | None                                                        |
| [manage_notifications.php](../../manage_notifications.php)                                                                       | PUBLIC           | None                                                        |
| [manage_nudges.php](../../manage_nudges.php)                                                                                     | PUBLIC           | None                                                        |
| [phpstan.neon](../../phpstan.neon)                                                                                               | TODO             | TODO - Not sure yet                                         |
| [settings.php](../../settings.php)                                                                                               | NONE             | None                                                        |
| [tests/README.md](../../tests/README.md)                                                                                         | REQUIRED         | Deny tests directory AND all markdown files                 |
| [tests/issues/README.md](../../tests/issues/README.md)                                                                           | REQUIRED         | Deny tests directory AND all markdown files                 |
| [tests/lib_test.php](../../tests/lib_test.php)                                                                                   | REQUIRED         | Deny tests directory                                        |
| [tests/phpstan/bootstrap.php](../../tests/phpstan/bootstrap.php)                                                                 | REQUIRED         | Deny tests directory                                        |
| [tests/phpunit.xml](../../tests/phpunit.xml)                                                                                     | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/dml/.gitkeep](../../tests/testclasses/dml/.gitkeep)                                                           | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/event/nudge_event_test.php](../../tests/testclasses/event/nudge_event_test.php)                               | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/form/nudge/edit_test.php](../../tests/testclasses/form/nudge/edit_test.php)                                   | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/form/nudge_notification/edit_test.php](../../tests/testclasses/form/nudge_notification/edit_test.php)         | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/local/abstract_nudge_entity_test.php](../../tests/testclasses/local/abstract_nudge_entity_test.php)           | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/local/nudge_notification_content_test.php](../../tests/testclasses/local/nudge_notification_content_test.php) | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/local/nudge_notification_test.php](../../tests/testclasses/local/nudge_notification_test.php)                 | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/local/nudge_test.php](../../tests/testclasses/local/nudge_test.php)                                           | REQUIRED         | Deny tests directory                                        |
| [tests/testclasses/task/.gitkeep](../../tests/testclasses/task/.gitkeep)                                                         | REQUIRED         | Deny tests directory AND dotfiles                           |
| [version.php](../../version.php)                                                                                                 | NONE             | None                                                        |