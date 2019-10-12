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

namespace Hyn\Tenancy\Generators\Database;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Contracts\Foundation\Application;

class DefaultPasswordGenerator implements PasswordGenerator
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Keep env secret
     */
    private $secret = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

        /**
         * Get env secret
         */
        $this->secret = env('APP_KEY');
    }

    /**
     * Custom pwd generator
     * @param Website $website
     * @return string
     */
    public function generate(Website $website) : string
    {
        $aux = "{$website->id}{$website->uuid}{$this->secret}";

        //Start the pwd with some letter, because symbols can cause errors
        $pwd = substr('BR'.md5($aux), 0, 12); 
        
        ///\Log::info("PWD generated for new tenant: $website->uuid");
        return $pwd;
    }

    /*
     *Default Hyn funcion to PWD
    public function generate(Website $website) : string
    {
        $key = $this->app['config']->get('tenancy.key');

        // Backward compatibility
        if ($key === null) {
            return md5(sprintf(
                '%s.%d',
                $this->app['config']->get('app.key'),
                $website->id
            ));
        }

        return md5(sprintf(
            '%d.%s.%s.%s',
            $website->id,
            $website->uuid,
            $website->created_at,
            $key
        ));
    }
    */
}
