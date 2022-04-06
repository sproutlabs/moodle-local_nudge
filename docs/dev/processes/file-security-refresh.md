1. Run `git ls-files > example.txt`
1. Open [File Security](../05-file-security.md).
1. Check each line in `example.txt`.
1. <a name="for-each-file">For each file</a> (line in `example.txt`)
    1. If a file is already excluded by sane webserver rules still note it as `REQUIRED` and explain what should stop it from being served directly.
    1. If a file should **NOT** be directly access from the browser make sure it includes a constant or die check:
        ```php
        defined('MOODLE_INTERNAL') || die();
        ```
    1. If a file has a constant or die check mark it as `NONE`.
    1. If a file should be accessed directly from the web mark it as `PUBLIC`.
    1. Repeat from [For each file](#for-each-file)
1. Done.
