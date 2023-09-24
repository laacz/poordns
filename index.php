<?php

// default configuration when using this script, create config.php with overrides
// see readme if unclear
$db_host = '';
$db_user = '';
$db_pass = '';
$db_base = 'pdns';
$pri_dns = 'ns-pri.example.com';
$user = '';
$pass = '';

// File might not be present, then we'll use the above values
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    ?>
    <h1>Script not set up</h1>
    <p>Please create a file named <code>config.php</code> with the following contents:</p>

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

    DB::init($db_host, $db_user, $db_pass, $db_base);

    handlePost('delete-domain', function () {
        $domain = Domain::find($_POST['id']);
        $domain->delete();
        foreach (Record::all(['domain_id' => $domain->id]) as $record) {
            $record->delete();
        }
        header('Location: ' . uri(''));
        return '';
    });

    handlePost('add-domain', function () {
        global $pri_dns;
        $domain = new Domain();
        $domain->name = $_POST['name'];
        $domain->type = 'MASTER';
        $domain->save();
        $soa = new Record();
        $soa->type = 'SOA';
        $soa->name = $domain->name;
        $soa->content = "$pri_dns. hostmaster.$pri_dns. " . date('Ymd') . '01 10800 3600 604800 3600';
        $soa->ttl = 3600;
        $soa->domain_id = $domain->id;
        $soa->save();
        header('Location: ' . uri('domain', array('id' => $domain->id)));
        return '';
    });

    handlePost('add_record', function () {
        $domain = Domain::find($_POST['domain_id']);
        if ($domain) {
            $soa = $domain->soa();

            if (($_POST['new_content'] ?? '') !== '') {
                $record = new Record();
                $record->type = $_POST['new_type'] ?? null;
                $record->name = $_POST['new_name'] ?? null;
                if (!str_ends_with($record->name, '.' . $domain->name)) {
                    $record->name .= '.' . $domain->name;
                }
                $record->name = trim($record->name, '.');
                $record->content = $_POST['new_content'] ?? null;
                $record->ttl = (isset($_POST['new_ttl']) && (int)$_POST['new_ttl']) ? (int)$_POST['new_ttl'] : SOA::from($soa->content)->ttl;
                $record->prio = (isset($_POST['new_prio']) && (int)$_POST['new_prio']) ? (int)$_POST['new_prio'] : null;
                $record->domain_id = $domain->id;
                $record->save();
                $soa->content = SOA::from($soa->content)->incrementSerial() . '';
                $soa->save();
            }
            header('Location: ' . uri('domain', array('id' => $domain->id)));
        } else {
            dd('no domain');
        }

    });

    handlePost('save-domain', function () {
        $domain = Domain::find($_POST['domain_id']);
        if ($domain) {
            $soa = $domain->soa();

            $updated = false;
            foreach ($_POST['type'] ?? [] as $id => $input) {
                if ($input === '') {
                    continue;
                }
                $record = Record::find($id);

                $dirty = false;
                foreach (['type' => 'string', 'name' => 'string', 'content' => 'string', 'ttl' => 'int', 'prio' => 'int'] as $field => $type) {
                    $val = $type == 'int' ? (int)$_POST[$field][$id] : $_POST[$field][$id];
                    if ($record->$field !== $val) {
                        $record->$field = $val;
                        $dirty = true;
                    }
                }
                if ($dirty) {
                    $updated = true;
                    $record->save();
                }
            }

            if ($updated) {
                $soa->content = SOA::from($soa->content)->incrementSerial() . '';
                $soa->save();
            }

            header('Location: ' . uri('domain', array('id' => $domain->id)));
        } else {
            dd('no domain');
        }
    });

    handlePost('delete-record', function () {
        if (!isset($_POST['record_id'])) {
            dd('no record_id');
        }
        $record = Record::find($_POST['record_id']);
        $domain = Domain::find($record->domain_id);
        $record->delete();
        header('Location: ' . uri('domain', array('id' => $domain->id)));
    });

    handle('', function () {
        global $domains;
        $domains = [];
        $domains = Domain::all();
        $page = 'domains';
        require 'pages/domains.php';
    });

    handle('domain', function () {
        global $domain, $default_ttl;
        $domain = Domain::find($_GET['id']);
        $page = 'domain';
        $default_ttl = SOA::from($domain->soa()->content)->ttl;
        require('pages/domain.php');
    });

    if (!handled()) {
        require('pages/404.php');
    };

} catch (Exception $e) {
    echo '<pre>' . $e->getMessage() . '</pre>';
}
