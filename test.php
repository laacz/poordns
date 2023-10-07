<?php

require('database.php');
require('common.php');
require('controllers.php');

function test_soa_serial_incrementor(): void
{
    $soa = new SOA('ns1.example.com.', 'hostmaster.example.com.', date('Ymd') . '01', 3600, 600, 604800, 3600);
    $soa->serial = '1';
    $soa->incrementSerial();
    assert($soa->serial == date('Ymd') . '01');
    $soa->incrementSerial();
    assert($soa->serial == date('Ymd') . '02');
    $soa->serial = date('Ymd') . '121';
    $soa->incrementSerial();
    assert($soa->serial == date('Ymd') . '122');
}


function test_soa(): void
{
    $soa = SOA::from('ns1.example.com. hostmaster.example.com. ' . date('Ymd') . '01 3600 600 604800 3600');
    assert($soa->primary == 'ns1.example.com.');
    assert($soa->email == 'hostmaster.example.com.');
    assert($soa->serial == date('Ymd') . '01');
    assert($soa->refresh == '3600');
    assert($soa->retry == '600');
    assert($soa->expire == '604800');
    assert($soa->ttl == '3600');
    assert((string)$soa == 'ns1.example.com. hostmaster.example.com. ' . date('Ymd') . '01 3600 600 604800 3600');
    assert((string)$soa->incrementSerial() == 'ns1.example.com. hostmaster.example.com. ' . date('Ymd') . '02 3600 600 604800 3600');
}

function test_handled(): void
{
    handled(false);
    assert(handled() === false);
    handled(true);
    assert(handled() === true);
    handled(false);
    assert(handled() === false);
}

function test_input(): void
{
    $_GET['test'] = 'test';
    assert(input('test') === 'test');
    $_POST['test'] = 'test2';
    assert(input('test') === 'test2');
    assert(input('test2') === null);
    assert(input('test2', 'default') === 'default');
}

function test_uri(): void
{
    $_SERVER['SCRIPT_NAME'] = '/test.php';
    assert(uri('test') === '/test.php?page=test');
    assert(uri('test', ['test' => 'test']) === '/test.php?test=test&page=test');
    assert(uri('test', ['test' => 'test', 'test2' => 'test2']) === '/test.php?test=test&test2=test2&page=test');
}

function test_errors(): void
{
    assert(errors() === []);
    errors('test');
    assert(errors() === ['test']);
    errors('test2');
    assert(errors() === ['test', 'test2']);
}

function test_get_handler(): void
{
    // Handles GET requests
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['page'] = 'test';
    handled(false);
    handle('test', fn() => '');
    assert(handled() === true);

    // Does not handle requests it should not handle
    $_GET['page'] = 'testing';
    handled(false);
    handle('test', fn() => '');
    assert(handled() === false);

    // Case-insensitive
    $_SERVER['REQUEST_METHOD'] = 'get';
    $_GET['page'] = 'test';
    handled(false);
    handle('test', fn() => '');
    assert(handled() === true);

    // Does not handle POST requests
    $_SERVER['REQUEST_METHOD'] = 'post';
    $_GET['page'] = 'test';
    handled(false);
    handle('test', fn() => '');
    assert(handled() === false);

    // Default method is GET
    unset($_SERVER['REQUEST_METHOD']);
    $_GET['page'] = 'test';
    handled(false);
    handle('test', fn() => '');
    assert(handled() === true);
}

function test_post_handler(): void
{
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['action'] = 'test';
    handled(false);
    handlePost('test', fn() => '');
    assert(handled() === true);

    $_POST['action'] = 'testing';
    handled(false);
    handlePost('test', fn() => '');
    assert(handled() === false);

    $_SERVER['REQUEST_METHOD'] = 'post';
    $_POST['action'] = 'test';
    handled(false);
    handlePost('test', fn() => '');
    assert(handled() === true);

    $_SERVER['REQUEST_METHOD'] = 'get';
    $_POST['action'] = 'test';
    handled(false);
    handlePost('test', fn() => '');
    assert(handled() === false);
}

function test_list_domains(): void
{
    $domain = new Domain();
    $domain->name = 'test.local';
    $domain->type = 'MASTER';
    $domain->save();

    $domain = new Domain();
    $domain->name = 'test2.local';
    $domain->type = 'MASTER';
    $domain->save();

    ob_start();
    listDomains();
    $resp = ob_get_clean();
    assert(str_contains($resp, 'test.local'));
    assert(str_contains($resp, 'test2.local'));
}

function test_add_domain(): void
{
    $_POST['name'] = 'test.local';
    addDomain();

    $domain = Domain::find(1);
    assert($domain->name === 'test.local');
    assert(count($domain->records()) === 1);
    $record = $domain->records()[0];
    assert($record->type === 'SOA');
}

function test_show_domain(): void
{
    $_POST['name'] = 'test.local';
    addDomain();

    $_GET['id'] = 1;

    ob_start();
    showDomain();
    $resp = ob_get_clean();
    assert(str_contains($resp, 'test.local'));
}

function test_add_record(): void
{
    $_POST['name'] = 'test.local';
    addDomain();

    $_POST['domain_id'] = 1;
    $_POST['new_name'] = 'test.local';
    $_POST['new_type'] = 'A';
    $_POST['new_content'] = '127.0.0.2';
    $_POST['new_ttl'] = '3600';
    addRecord();

    $domain = Domain::find(1);
    assert(count($domain->records()) === 2);
    $record = $domain->records()[1];
    assert($record->type === 'A');
    assert($record->content === '127.0.0.2');
    assert($record->ttl === 3600);
}

function test_update_record(): void
{
    test_add_record();

    $records = Record::all(['domain_id' => 1]);
    $_POST['id'] = $_POST['name'] = $_POST['type'] = $_POST['content'] = $_POST['ttl'] = $_POST['prio'] = [];
    foreach ($records as $record) {
        if ($record->type === 'SOA') {
            continue;
        }
        $_POST['name'][$record->id] = $record->name;
        $_POST['type'][$record->id] = $record->type;
        $_POST['content'][$record->id] = $record->content;
        $_POST['ttl'][$record->id] = $record->ttl;
        $_POST['prio'][$record->id] = $record->prio;
    }

    $_POST['name'][2] = 'test1.local';
    $_POST['type'][2] = 'B';
    $_POST['content'][2] = '127.0.0.3';
    $_POST['ttl'][2] = '3601';
    saveDomain();

    $domain = Domain::find(1);
    assert(count($domain->records()) === 2);
    $record = $domain->records()[1];
    assert($record->type === 'B');
    assert($record->content === '127.0.0.3');
    assert($record->ttl === 3601);
}

function test_delete_record(): void
{
    test_add_record();

    $records = Record::all(['domain_id' => 1]);
    $_POST['record_id'] = $records[1]->id;
    deleteRecord();

    $domain = Domain::find(1);
    assert(count($domain->records()) === 1);
}

function test_delete_domain(): void
{
    test_add_domain();

    $domain = Domain::find(1);
    $_POST['id'] = $domain->id;
    deleteDomain();

    assert(count(Domain::all()) === 0);
}

function test_search(): void
{
    test_add_record();

    $_GET['q'] = 'test.local';
    ob_start();
    search();
    $resp = ob_get_clean();
    assert(str_contains($resp, 'test.local'));

    $_GET['q'] = '127.0.0.2';
    ob_start();
    search();
    $resp = ob_get_clean();
    assert(str_contains($resp, 'test.local'));

    $_GET['q'] = 'MZGD';
    ob_start();
    search();
    $resp = ob_get_clean();
    assert(!str_contains($resp, 'test.local'));
}

// Now we are running all test_* functions in this file
$functions = get_defined_functions()['user'];
foreach ($functions as $function) {
    if (str_starts_with($function, 'test_')) {
        $ref = new ReflectionFunction($function);
        if ($ref->getFileName() !== __FILE__) {
            continue;
        }
        echo "Running $function...\n";
        init_test_db();
        $function();
    }
}

unlink('test.db');

// this one refreshes and initialises database
function init_test_db(): void
{
    if (file_exists('test.db')) {
        unlink('test.db');
    }

    DB::init('', '', '', 'test.db', 'sqlite');
    $schema = <<<SQL
        PRAGMA foreign_keys = 1;
        
        CREATE TABLE domains (
          id                    INTEGER PRIMARY KEY,
          name                  VARCHAR(255) NOT NULL COLLATE NOCASE,
          master                VARCHAR(128) DEFAULT NULL,
          last_check            INTEGER DEFAULT NULL,
          type                  VARCHAR(8) NOT NULL,
          notified_serial       INTEGER DEFAULT NULL,
          account               VARCHAR(40) DEFAULT NULL,
          options               VARCHAR(65535) DEFAULT NULL,
          catalog               VARCHAR(255) DEFAULT NULL
        );
        
        CREATE UNIQUE INDEX name_index ON domains(name);
        CREATE INDEX catalog_idx ON domains(catalog);
        
        
        CREATE TABLE records (
          id                    INTEGER PRIMARY KEY,
          domain_id             INTEGER DEFAULT NULL,
          name                  VARCHAR(255) DEFAULT NULL,
          type                  VARCHAR(10) DEFAULT NULL,
          content               VARCHAR(65535) DEFAULT NULL,
          ttl                   INTEGER DEFAULT NULL,
          prio                  INTEGER DEFAULT NULL,
          disabled              BOOLEAN DEFAULT 0,
          ordername             VARCHAR(255),
          auth                  BOOL DEFAULT 1,
          FOREIGN KEY(domain_id) REFERENCES domains(id) ON DELETE CASCADE ON UPDATE CASCADE
        );
        
        CREATE INDEX records_lookup_idx ON records(name, type);
        CREATE INDEX records_lookup_id_idx ON records(domain_id, name, type);
        CREATE INDEX records_order_idx ON records(domain_id, ordername);
        
        
        CREATE TABLE supermasters (
          ip                    VARCHAR(64) NOT NULL,
          nameserver            VARCHAR(255) NOT NULL COLLATE NOCASE,
          account               VARCHAR(40) NOT NULL
        );
        
        CREATE UNIQUE INDEX ip_nameserver_pk ON supermasters(ip, nameserver);
        
        
        CREATE TABLE comments (
          id                    INTEGER PRIMARY KEY,
          domain_id             INTEGER NOT NULL,
          name                  VARCHAR(255) NOT NULL,
          type                  VARCHAR(10) NOT NULL,
          modified_at           INT NOT NULL,
          account               VARCHAR(40) DEFAULT NULL,
          comment               VARCHAR(65535) NOT NULL,
          FOREIGN KEY(domain_id) REFERENCES domains(id) ON DELETE CASCADE ON UPDATE CASCADE
        );
        
        CREATE INDEX comments_idx ON comments(domain_id, name, type);
        CREATE INDEX comments_order_idx ON comments (domain_id, modified_at);
        
        
        CREATE TABLE domainmetadata (
         id                     INTEGER PRIMARY KEY,
         domain_id              INT NOT NULL,
         kind                   VARCHAR(32) COLLATE NOCASE,
         content                TEXT,
         FOREIGN KEY(domain_id) REFERENCES domains(id) ON DELETE CASCADE ON UPDATE CASCADE
        );
        
        CREATE INDEX domainmetaidindex ON domainmetadata(domain_id);
        
        
        CREATE TABLE cryptokeys (
         id                     INTEGER PRIMARY KEY,
         domain_id              INT NOT NULL,
         flags                  INT NOT NULL,
         active                 BOOL,
         published              BOOL DEFAULT 1,
         content                TEXT,
         FOREIGN KEY(domain_id) REFERENCES domains(id) ON DELETE CASCADE ON UPDATE CASCADE
        );
        
        CREATE INDEX domainidindex ON cryptokeys(domain_id);
        
        
        CREATE TABLE tsigkeys (
         id                     INTEGER PRIMARY KEY,
         name                   VARCHAR(255) COLLATE NOCASE,
         algorithm              VARCHAR(50) COLLATE NOCASE,
         secret                 VARCHAR(255)
        );
        
        CREATE UNIQUE INDEX namealgoindex ON tsigkeys(name, algorithm);
        SQL;

    foreach (explode(';', $schema) as $sql) {
        if (strlen(trim($sql))) {
            DB::query($sql);
        }
    }

}

