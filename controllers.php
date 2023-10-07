<?php

function listDomains(): void
{
    global $domains;
    $domains = [];
    $domains = Domain::all();

    require 'pages/domains.php';
}

function showDomain(): void
{
    global $domain, $default_ttl;
    $domain = Domain::find($_GET['id']);
    $default_ttl = SOA::from($domain->soa()->content)->ttl;

    require('pages/domain.php');
}

function addDomain(): void
{
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

    if (!php_sapi_name() == 'cli') {
        header('Location: ' . uri('domain', array('id' => $domain->id)));
    }
}

function deleteRecord(): void
{
    if (!isset($_POST['record_id'])) {
        dd('no record_id');
    }
    $record = Record::find($_POST['record_id']);
    $domain = Domain::find($record->domain_id);
    $record->delete();

    if (!php_sapi_name() == 'cli') {
        header('Location: ' . uri('domain', array('id' => $domain->id)));
    }

}

function saveDomain(): void
{
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
                $val = $type == 'int'
                    ? (int)($_POST[$field][$id] ?? 0)
                    : ($_POST[$field][$id] ?? null);
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

        if (!php_sapi_name() == 'cli') {
            header('Location: ' . uri('domain', array('id' => $domain->id)));
        }
    } else {
        dd('no domain');
    }
}

function addRecord(): void
{
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

        if (!php_sapi_name() == 'cli') {
            header('Location: ' . uri('domain', array('id' => $domain->id)));
        }
    } else {
        dd('no domain');
    }
}

function deleteDomain(): void
{
    $domain = Domain::find($_POST['id']);
    foreach (Record::all(['domain_id' => $domain->id]) as $record) {
        $record->delete();
    }
    $domain->delete();
    if (!php_sapi_name() == 'cli') {
        header('Location: ' . uri(''));
    }
}

function search(): void
{
    $q = '%' . $_GET['q'] . '%';
    $sql = '
        SELECT * 
        FROM domains 
        WHERE name LIKE ?
           OR id IN (
               SELECT domain_id
               FROM records 
               WHERE name LIKE ? OR content LIKE ?
           )';
    $domains = DB::query($sql, [$q, $q, $q])->fetchAll(PDO::FETCH_CLASS, Domain::class);

    $sql = 'SELECT * FROM records WHERE name LIKE ? OR content LIKE ?';
    $records = DB::query($sql, [$q, $q])->fetchAll(PDO::FETCH_CLASS, Record::class);

    require('pages/search.php');
}