# Testing

PHPStan is in here to avoid access of the moodle internal declaration via web.

Quick n easy alias for testing:
```SHELL
function tt() {
    clear;
    if  [ -z "$1" ]; then
        echo vendor/bin/phpunit -c local/nudge/tests/phpunit.xml --color;
        vendor/bin/phpunit -c local/nudge/tests/phpunit.xml --color
    else
        echo vendor/bin/phpunit -c local/nudge/tests/phpunit.xml --color --filter $1;
        vendor/bin/phpunit -c local/nudge/tests/phpunit.xml --color --filter $1
    fi
}
```