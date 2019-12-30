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
                $this->createDatabase($connection, $config) &&
                $this->createUser($connection, $config) &&
                $this->grantPrivileges($connection, $config);
        } else {
            return $this->createDatabase($connection, $config);
        }
    }

    protected function createDatabase(IlluminateConnection $connection, array $config)
    {
        $usernameGlobal = env('TENANCY_USERNAME', null);
        
        return $connection->statement("
            IF NOT EXISTS (
                SELECT * FROM sys.databases WHERE 
                name = '{$config['database']}'
            ) BEGIN
                CREATE DATABASE {$config['database']};
            END");
    }

    protected function createUser(IlluminateConnection $connection, array $config)
    {
        $username = $config['username'];
         $database = $config['database'];

        return $connection->statement("
        IF NOT EXISTS(
            SELECT principal_id FROM sys.server_principals 
            WHERE name = '{$username}'
            ) BEGIN 
                USE {$database};
                CREATE LOGIN {$username} 
                WITH PASSWORD = '{$config['password']}', 
                DEFAULT_DATABASE = {$database},
                DEFAULT_LANGUAGE = English;
                CREATE USER {$username} 
                FOR LOGIN {$username}; 
            END");
    }

    protected function grantPrivileges(IlluminateConnection $connection, array $config)
    {
        $usernameGlobal = env('TENANCY_USERNAME', null);
        $databaseGlobal = env('TENANCY_DATABASE', null);

        return $connection->statement("
            USE {$config['database']}; 
            GRANT ALL TO {$usernameGlobal};
            GRANT ALL TO {$config['username']};
            GRANT ALTER, CONTROL ON SCHEMA::dbo TO {$usernameGlobal}; 
            GRANT ALTER, CONTROL ON SCHEMA::dbo TO {$config['username']}; 
            USE {$databaseGlobal};
        ");
    }

    //not implemented
    public function updated(Updated $event, array $config, Connection $connection): bool
    {
        return false;
    }

    //not implemented
    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        return false;
    }

    //not implemented
    public function updatePassword(Website $website, array $config, Connection $connection): bool
    {
        return false;
    }
}