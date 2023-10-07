<?php

// default configuration when using this script, create config.php with overrides
// see readme if unclear
$db_host = '';
$db_user = '';
$db_pass = '';
$db_base = 'pdns';
// SOA related stuff
$pri_dns = 'ns-pri.example.com';
// User and password for HTTP basic authentication
$user = '';
$pass = '';

// File might not be present, then we'll use the above values. That's for those who
// really want to have a single file.
if (file_exists('config.php')) {
    require_once 'config.php';
}

if ($db_user === '') {
    ?>
    <h1>Script not set up</h1>
    <p>Please create a file named <code>config.php</code> with the following contents, or edit variables at the top of this file:</p>

    <pre>
&lt;?php
// MySQL related configuration
$db_host = 'database host (empty means localhost)';
$db_user = 'mysql username';
$db_pass = 'mysql password';
$db_base = 'mysql database';

// SOA related stuff
$pri_dns = 'primary nameserver hostname (FQDN)';

// Following MUST be set if not behind other authentication
$user = 'http basic authentication username';
$pass = 'http basic authentication password';</pre>
    <?php
    die();
}

if ("$user$pass" !== '') {
    if (
        !isset($_SERVER['PHP_AUTH_USER']) ||
        !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== $user ||
        $_SERVER['PHP_AUTH_PW'] !== $pass
    ) {
        header('WWW-Authenticate: Basic realm="PowerDNS"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Unauthorized';
        exit;
    }
}

try {
    require('common.php');
    require('database.php');
    require('controllers.php');

    DB::init($db_host, $db_user, $db_pass, $db_base);

    handlePost('delete-domain', 'deleteDomain');
    handlePost('add-domain', 'addDomain');
    handlePost('add-record', 'addRecord');
    handlePost('save-domain', 'saveDomain');
    handlePost('delete-record', 'deleteRecord');

    handle('domain', 'showDomain');
    handle('search', 'search');
    handle('', 'listDomains');

    if (!handled()) {
        require('pages/404.php');
    };

} catch (Exception $e) {
    echo '<pre>' . $e->getMessage() . '</pre>';
}
