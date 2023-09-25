<?php

/**
 * Returns URI for given page and parameters.
 */
function uri(string $page, array $params = []): string
{
    $params['page'] = $page;
    return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($params);
}

/**
 * Class used to deal with SOA records.
 */
class SOA
{
    public string $primary;
    public string $email;
    public int $serial;
    public int $refresh;
    public int $retry;
    public int $expire;
    public int $ttl;

    public static function from(string $string): SOA
    {
        return new self(...explode(' ', $string));
    }

    public function __construct(string $primary, string $email, int $serial, int $refresh, int $retry, int $expire, int $ttl)
    {
        $this->primary = $primary;
        $this->email = $email;
        $this->serial = $serial;
        $this->refresh = $refresh;
        $this->retry = $retry;
        $this->expire = $expire;
        $this->ttl = $ttl;
    }

    public function incrementSerial(): SOA
    {
        // serial contains of YYYYMMDDNN, where YYYYMMDD is current date and NN is the next sequence number, starting at 1
        $date = date('Ymd');
        $sequence = intval(substr($this->serial, 8), 10);
        if (substr($this->serial, 0, 8) == $date) {
            $sequence++;
        } else {
            $sequence = 1;
        }

        $this->serial = $date . sprintf('%02d', $sequence);

        return $this;
    }

    public function __toString(): string
    {
        return $this->primary . ' ' . $this->email . ' ' . $this->serial . ' ' . $this->refresh . ' ' . $this->retry . ' ' . $this->expire . ' ' . $this->ttl;
    }
}

// Who wouldn't want to dump?
function dump(mixed ...$vars): void
{
    foreach ($vars as $var) {
        echo '<pre style="border: 1px solid #ccc; padding: .5rem;">';
        print_r($var);
        echo '</pre>';
    }
}

// Who whouldn't want to dump and die?
#[NoReturn] function dd(mixed ...$vars): void
{
    dump(...$vars);
    die;
}

function handled($val = null)
{
    static $handled = false;
    if ($val !== null) {
        $handled = $val;
    }
    return $handled;
}

// POST handler
function handlePost(string $action, callable $callback): void
{
    if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') === 0 && (($_POST['action'] ?? '') === $action)) {
        handled(true);
        echo $callback();
    }
}

// GET handler
function handle(string $page, callable $callback): void
{
    if (strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') === 0 && (($_GET['page'] ?? '') === $page)) {
        handled(true);
        echo $callback();
    }
}

function recordTypes(): array
{
    return [
        'A',
        'A6',
        'AAAA',
        'AFSDB',
        'ALIAS',
        'APL',
        'CAA',
        'CDNSKEY',
        'CDS',
        'CERT',
        'CNAME',
        'CSYNC',
        'DHCID',
        'DLV',
        'DNAME',
        'DNSKEY',
        'DS',
        'EUI48',
        'EUI64',
        'HINFO',
        'HTTPS',
        'IPSECKEY',
        'KEY',
        'KX',
        'L32',
        'L64',
        'LOC',
        'LP',
        'MAILA',
        'MAILB',
        'MINFO',
        'MR',
        'MX',
        'NAPTR',
        'NID',
        'NS',
        'NSEC',
        'NSEC3',
        'NSEC3PARAM',
        'OPENPGPKEY',
        'PTR',
        'RKEY',
        'RP',
        'RRSIG',
        'SIG',
        'SMIMEA',
        'SOA',
        'SPF',
        'SRV',
        'SSHFP',
        'SVCB',
        'TKEY',
        'TLSA',
        'TSIG',
        'TXT',
        'URI',
        'WKS',
    ];
}

function hl(string $within, string $search): string
{
    return str_replace(
        $search,
        '<span style="background-color: #ff0;">' . $search . '</span>',
        $within,
    );
}