# PoorDNS

Simple PowerDNS MySQL backend management interface within one PHP file. Spaghetti, of course.

## Limitations

* Only supports MySQL backend
* No validation of input data (be careful)
* No support for DNSSEC

## Installation

Download from releases, upload to server, add `config.php` (see below), profit.

## Configuration

Configuration is done via config.php. Of course, you could do that in the release file itself, but that would entail losing config data when updating.

```php
<?php
// MySQL related configuration
$db_host = 'database host (empty means localhost)';
$db_user = 'mysql username';
$db_pass = 'mysql password';
$db_base = 'mysql database';

// SOA related stuff
$pri_dns = 'primary nameserver hostname (FQDN)';

// Following MUST be set if not behind other authentication
$user = 'http basic authentication username';
$pass = 'http basic authentication password';
```

## Building yourself

If you want to build it yourself, just do this. I can't fathom why you'd need to, though.

```bash
php build.php >release.php
```