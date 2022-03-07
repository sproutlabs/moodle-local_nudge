<?php
// phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
// phpcs:disable

/** Used by library scripts to check they are being called by Moodle */
define('MOODLE_INTERNAL', true);

/** Software maturity level - internals can be tested using white box techniques. */
define('MATURITY_ALPHA',    50);
/** Software maturity level - feature complete, ready for preview and testing. */
define('MATURITY_BETA',     100);
/** Software maturity level - tested, will be released unless there are fatal bugs. */
define('MATURITY_RC',       150);
/** Software maturity level - the latest rolling Totara release. */
define('MATURITY_EVERGREEN',  190);
/** Software maturity level - ready for production deployment. */
define('MATURITY_STABLE',   200);
/** Any version - special value that can be used in $plugin->dependencies in version.php files. */
define('ANY_VERSION', 'any');