<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Generators\Webserver\Database\Drivers;

use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Deleted;
use Hyn\Tenancy\Events\Websites\Updated;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Illuminate\Database\Connection as IlluminateConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SQLServer implements DatabaseGenerator
{
    //Lost repository that was done
    //Need to finish the implementation again
    //Link to help: https://github.com/Gadeoli/hyn.tenant.sqlserver.git

    //Sqlserver Create
    public function created(Created $event, array $config, Connection $connection): bool
    {
        $connection = $connection->system($event->website);

        $createUser = config('tenancy.db.auto-create-tenant-database-user', true);

        if ($createUser) {
            return
                $this->createUser($connection, $config) &&
                $this->createDatabase($connection, $config) &&
                $this->grantPrivileges($connection, $config);
        } else {
            return $this->createDatabase($connection, $config);
        }
    }

    protected function createUser(IlluminateConnection $connection, array $config)
    {
        return $connection->statement("IF NOT EXISTS(SELECT principal_id FROM sys.server_principals WHERE name = '{$config['username']}') BEGIN CREATE LOGIN {$config['username']} WITH PASSWORD = '{$config['password']}' END 
        IF NOT EXISTS (SELECT principal_id FROM sys.database_principals WHERE name = '{$config['username']}') BEGIN CREATE USER {$config['username']} FOR LOGIN {$config['username']} END");
    }

    protected function createDatabase(IlluminateConnection $connection, array $config)
    {
        return $connection->statement("IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = '{$config['database']}') CREATE DATABASE {$config['database']};");
    }

    protected function grantPrivileges(IlluminateConnection $connection, array $config)
    {
        $privileges = config('tenancy.db.tenant-database-user-privileges', null) ?? 'ALL';
            return $connection->statement("USE {$config['database']} GRANT $privileges TO {$config['username']}");
    }
}
