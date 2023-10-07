# PoorDNS

Simple PowerDNS MySQL backend management interface within one PHP file. Spaghetti, of course.

I've used it myself for years (decades if you will). It's a bit refactored, bulmified and (maybe) released for the world
to see.

## Limitations

* No validation of input data (be careful)
* No support for DNSSEC

## Installation

Download index.php [from releases](https://github.com/laacz/poordns/releases), upload to server, add `config.php` (see
below), profit.

## Configuration

Configuration is done via config.php. Of course, you could do that in the release file itself, but that would entail
losing config data when updating.

```php
<?php
// Database related configuration
$db_backend = 'mysql or sqlite';
$db_base = 'mysql database name or sqlite filename';
$db_host = 'database host (empty means localhost)';
$db_user = 'mysql username or empty if sqlite';
$db_pass = 'mysql password or empty if sqlite';

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