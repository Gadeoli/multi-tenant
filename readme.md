## Fork to sqlserver connection with hyn-tenant package

## Tested with enviroment:

- Win10
- SqlServer 2017 Developer
- Wamp Server with php 7.3
- ODBC 17

## DLL EXT TO PHP

<a href="https://github.com/Microsoft/msphpsql/releases">
    Download Sql PHP extension (take the right version)
</a>
Extract files.
Copy the right version to your php extension folder.
Add the new extension (pdo_sqlsrv and sqlsrv) to your php.ini file
extension=pdo_sqlsrv (...)
extension=sqlsrv (...)

## Create a new user and set him permissions (i don't like to use user sa / just set sysadmin)

Set user language if necessary:
```
Exec sp_defaultlanguage 'myuser', 'us_english'
Reconfigure
```

## add the repo in your composer.json than run composer update
```
"repositories": [
    {
        "name": "hyn/multi-tenant",
        "type": "vcs",
        "url": "https://github.com/Gadeoli/multi-tenant"
    }
],
"require": {
    ...,
    "hyn/multi-tenant": "dev-dev-fix",
    ...
}
```

## Configure your env vars:
.env
```
#Tenancy configs
AUTO_DELETE_TENANT_DIRECTORY=true
TENANCY_DATABASE_AUTO_DELETE=true
TENANCY_BASE_URL="mytesturl.com"
TENANCY_DATABASE_PREFIX="myprefixdb_"
TENANCY_ENGINE=InnoDB
TENANCY_SYSTEM_CONNECTION_NAME=sqlsrv_tenancy
TENANCY_HOST=localhost
TENANCY_PORT=
TENANCY_DATABASE=myprefixdbglobal
TENANCY_USERNAME=myuserglobal
TENANCY_PASSWORD=""
```

## Configure the connection (config/database.php)
```
'sqlsrv_tenancy' => [
    'driver' => 'sqlsrv',
    'url' => env('DATABASE_URL'),
    'host' => env('TENANCY_HOST', 'localhost'),
    'port' => env('TENANCY_PORT', ''),
    'database' => env('TENANCY_DATABASE', 'forge'),
    'username' => env('TENANCY_USERNAME', 'forge'),
    'password' => env('TENANCY_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
],
```
