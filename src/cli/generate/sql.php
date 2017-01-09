<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\CommandLine\Index as CommandLine;
use Cradle\Sink\Faucet\Schema;
use Cradle\Sql\SqlFactory;

use Cradle\Sink\Faucet\Installer;

return function($request, $response) {
    //get database
    $service = $this->package('global')->service('sql-main');

    if(!$service) {
        CommandLine::error('Database was not found in config/services.php');
    }

    $cwd = $request->getServer('PWD');
    $schemaRoot = $cwd . '/schema';

    if(!is_dir($schemaRoot)) {
        return CommandLine::error('Schema folder not found. Generator Aborted.');
    }

    //Available schemas
    $available = [];
    $paths = scandir($schemaRoot, 0);
    foreach($paths as $path) {
        if(strpos($path, '.') === 0) {
            continue;
        }

        if(!is_dir($schemaRoot . '/' . $path)
            && !file_exists($schemaRoot . '/' . $path)
        )
        {
            continue;
        }

        $available[] = pathinfo($path, PATHINFO_FILENAME);
    }

    if(empty($available)) {
        return CommandLine::error('No available schemas found in ' . $schemaRoot);
    }

    //determine the active schema
    $active = $request->getStage('schema');

    if(!$active) {
        CommandLine::info('Available schemas:');
        foreach($available as $name) {
            CommandLine::info(' - ' . $name);
        }

        $active = CommandLine::input('Which schema to use?');
    }

    if(!in_array($active, $available)) {
        return CommandLine::error('Invalid schema. Generator Aborted.');
    }

    //it is possible that the active schema has multiple schemas
    $schemas = [];
    if(file_exists($schemaRoot . '/' . $active . '.php')) {
        $schemas[] = $active;
    } else if(is_dir($schemaRoot . '/' . $active)) {
        $paths = scandir($schemaRoot . '/' . $active, 0);

        foreach($paths as $path) {
            if($path === '.' || $path === '..' || substr($path, -4) !== '.php') {
                continue;
            }

            $schemas[] = $active . '/' . pathinfo($path, PATHINFO_FILENAME);
        }
    }

    CommandLine::system('Generating SQL...');

    $database = SqlFactory::load($service);

    $actionQueries = [];
    $createQueries = [];
    $dataQueries = [];

    foreach($schemas as $schema) {
        //get the template data
        $data = (new Schema($schemaRoot, $schema))->getData();

        //pre build the create, alter and placeholders
        $create = include __DIR__ . '/helper/sql/create.php';
        $placeholders = include __DIR__ . '/helper/sql/placeholders.php';

        //check for table
        $tables = $database->getTables($data['name']);

        $answer = 'i';
        if(in_array($data['name'], $tables)) {
            $message = '%s was found in database. Alter(a), Install(i) or skip(s)? (s)';
            $answer = CommandLine::input(sprintf($message, $data['name']), 'c');
        }

        if($answer === 'i') {
            $queries = $create;
        } else if($answer === 'a') {
            $queries = include __DIR__ . '/helper/sql/alter.php';
        } else {
            continue;
        }

        foreach($create as $query) {
            $createQueries[] = $query;
        }

        foreach($queries as $query) {
            $actionQueries[] = $query;
        }

        foreach($placeholders as $query) {
            $dataQueries[] = $query;
        }
    }

    //if nada
    if(empty($actionQueries) && empty($dataQueries)) {
        //dont continue
        return CommandLine::error('Nothing to add or change.');
    }

    if(!empty($actionQueries)) {
        CommandLine::system('Updating schema...');

        //determine the next version
        $moduleInstaller = $cwd . '/module/' . $active . '/install';

        if(!is_dir($moduleInstaller)) {
            mkdir($moduleInstaller, 0777, true);
        }

        $version = Installer::getNextVersion($active);

        $destination = $moduleInstaller . '/' . $version . '.sql';
        file_put_contents($destination, implode("\n\n", $actionQueries));

        $destination = $cwd . '/module/' . $active . '/schema.sql';
        file_put_contents($destination, implode("\n\n", $createQueries));

        $message = "-- Schema for %s module\n```\n%s\n```";
        CommandLine::info(sprintf($message, $active, implode("\n\n", $actionQueries)));
    }

    //placeholders
    if(!empty($dataQueries)) {
        CommandLine::system('Updating placeholders...');
        $destination = $cwd . '/module/' . $active . '/placeholder.sql';
        file_put_contents($destination, implode("\n\n", $dataQueries));

        $message = "-- Data for %s module\n```\n%s\n```";
        CommandLine::info(sprintf($message, $active, implode("\n\n", $placeholders)));
    }

    CommandLine::success('SQL files were generated.');
    CommandLine::info('Recommended actions:');
    CommandLine::info(' - bin/cradle faucet update');
    CommandLine::info(' - bin/cradle faucet build-sql');
    CommandLine::info(' - bin/cradle faucet flush-sql');
    CommandLine::info(' - bin/cradle faucet populate-sql');
};
