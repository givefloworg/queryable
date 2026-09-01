<?php

/**
 * The WordPress framework comes from wp-phpunit (a composer dev dependency),
 * and WP_TESTS_DIR only supplies wp-tests-config.php. Nothing here needs the
 * svn checkout the old installer made: the runner images stopped shipping svn.
 */
$wpTestsDir = getenv('WP_TESTS_DIR') ?: (static function (): string {
    $home       = getenv('HOME') ?: '';
    $candidates = array_filter([
        $home !== '' ? $home . '/.fundkit-wp-tests/wordpress-tests-lib' : null,
        rtrim(getenv('TMPDIR') ?: '/tmp', '/') . '/wordpress-tests-lib',
        '/tmp/wordpress-tests-lib',
    ]);

    foreach ($candidates as $dir) {
        if (file_exists($dir . '/wp-tests-config.php')) {
            return $dir;
        }
    }

    return rtrim(getenv('TMPDIR') ?: '/tmp', '/') . '/wordpress-tests-lib';
})();

$phpunitDir = getenv('WP_PHPUNIT__DIR') ?: dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';

$haveConfig    = file_exists($wpTestsDir . '/wp-tests-config.php');
$haveFramework = file_exists($phpunitDir . '/includes/functions.php');

if ($haveConfig && $haveFramework) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills');

    if (! defined('WP_TESTS_CONFIG_FILE_PATH')) {
        define('WP_TESTS_CONFIG_FILE_PATH', $wpTestsDir . '/wp-tests-config.php');
    }

    require_once $phpunitDir . '/includes/functions.php';

    tests_add_filter('muplugins_loaded', function () {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
    });

    require_once $phpunitDir . '/includes/bootstrap.php';
} elseif (getenv('QUERYABLE_REQUIRE_WP') === '1') {
    if (! $haveFramework) {
        fwrite(STDERR, "\nwp-phpunit is not at {$phpunitDir}. Did you run `composer install`?\n\n");
    } else {
        fwrite(STDERR, "\nThere is no wp-tests-config.php at {$wpTestsDir}.\n"
            . "Every file in this suite is a no-op without it, so the run would report\n"
            . "success having tested nothing. Run bin/install-wp-tests.sh, or set\n"
            . "WP_TESTS_DIR.\n\n");
    }
    exit(1);
} else {
    require_once __DIR__ . '/../vendor/autoload.php';

    defined('OBJECT') || define('OBJECT', 'OBJECT');
    defined('ARRAY_A') || define('ARRAY_A', 'ARRAY_A');
    defined('ARRAY_N') || define('ARRAY_N', 'ARRAY_N');
    defined('OBJECT_K') || define('OBJECT_K', 'OBJECT_K');
}
