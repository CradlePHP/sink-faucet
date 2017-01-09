<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\CommandLine\Index as CommandLine;

/**
 * CLI help menu
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
return function ($request, $response) {
    CommandLine::success('bin/cradle faucet install');
    CommandLine::info(' - Details: Installs Project');
    CommandLine::info(' - Example: bin/cradle faucet install');
    CommandLine::info(' - Example: bin/cradle faucet install --force --populate-sql');
    CommandLine::info(' - Example: bin/cradle faucet install testing_db -h 127.0.0.1 -u root -p root --force');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet update');
    CommandLine::info(' - Example: bin/cradle faucet update');
    CommandLine::info(' - Example: bin/cradle faucet update --module post');
    CommandLine::info(' - Details: Updates Project with versioning install scripts');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet server');
    CommandLine::info(' - Details: Starts up the PHP server (dev mode)');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-app');
    CommandLine::info(' - Details: Generates a new app folder');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-module');
    CommandLine::info(' - Example: bin/cradle faucet generate-module');
    CommandLine::info(' - Example: bin/cradle faucet generate-module --schema post');
    CommandLine::info(' - Details: Generates a new module given schema');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-admin');
    CommandLine::info(' - Example: bin/cradle faucet generate-admin');
    CommandLine::info(' - Example: bin/cradle faucet generate-admin --schema post');
    CommandLine::info(' - Details: Generates a new admin controller given schema');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-rest');
    CommandLine::info(' - Example: bin/cradle faucet generate-rest');
    CommandLine::info(' - Example: bin/cradle faucet generate-rest --schema post');
    CommandLine::info(' - Details: Generates a new REST controller given schema');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-sql');
    CommandLine::info(' - Example: bin/cradle faucet generate-sql');
    CommandLine::info(' - Example: bin/cradle faucet generate-sql --schema post');
    CommandLine::info(' - Details: Generates SQL given schema');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet generate-elastic');
    CommandLine::info(' - Example: bin/cradle faucet generate-elastic');
    CommandLine::info(' - Example: bin/cradle faucet generate-elastic --schema post');
    CommandLine::info(' - Details: Generates ElasticSearch map given schema');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet flush-sql');
    CommandLine::info(' - Example: bin/cradle faucet flush-sql');
    CommandLine::info(' - Example: bin/cradle faucet flush-sql --table post');
    CommandLine::info(' - Example: bin/cradle faucet flush-sql --tableset post');
    CommandLine::info(' - Details: Clears SQL database');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet build-sql');
    CommandLine::info(' - Details: Builds SQL schema on database');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet flush-elastic');
    CommandLine::info(' - Details: Clears the ElasticSearch index');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet map-elastic');
    CommandLine::info(' - Details: Builds an ElasticSearch schema map');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet populate-elastic');
    CommandLine::info(' - Details: Populates ElasticSearch index');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet populate-sql');
    CommandLine::info(' - Details: Populates SQL database');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet flush-redis');
    CommandLine::info(' - Details: Clears the Redis cache');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet queue [event] [data]');
    CommandLine::info(' - Details: Queues any event');
    CommandLine::info(' - Example: bin/cradle faucet queue auth-verify-mail auth_id=1 host=127.0.0.1');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet work');
    CommandLine::info(' - Details: Starts a worker');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet connect-to');
    CommandLine::info(' - Details: Gives the command to connect to a production server');
    CommandLine::info('   You need ask the faucet owner for the private key');
    CommandLine::info('   You need to setup config/deploy.php');
    CommandLine::info('   see: https://gist.github.com/cblanquera/3ff60b4c9afc92be1ac0a9d57afceb17#file-instructions-md');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet deploy-production');
    CommandLine::info(' - Details: Deploys code to production servers');
    CommandLine::info('   You need to setup config/deploy.php');
    CommandLine::warning('   Use with caution.');
    echo PHP_EOL;

    CommandLine::success('bin/cradle faucet deploy-s3');
    CommandLine::info(' - Details: Deploys public assets to AWS S3');
    CommandLine::info('   You need to setup config/services.php');
    CommandLine::warning('   Use with caution.');
    echo PHP_EOL;
};
