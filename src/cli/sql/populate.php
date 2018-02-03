<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\CommandLine\Index as CommandLine;
use Cradle\Sql\SqlFactory;
use Cradle\Sql\SqlException;

/**
 * CLI populates database with dummy data
 *
 * @param Request $request
 * @param Response $response
 */
return function ($request, $response) {
    CommandLine::system('Populating SQL...');

    $path = $this->package('global')->path('module');
    $folders = scandir($path, 0);

    $database = SqlFactory::load($this->package('global')->service('sql-main'));

    foreach ($folders as $folder) {
        if ($folder === '.' || $folder === '..' || !is_dir($path . '/' . $folder)) {
            continue;
        }

        $file = $path . '/' . $folder . '/placeholder.sql';

        if (!file_exists($file)) {
            continue;
        }

        //if we just want to populate one table
        if($request->hasStage('module') && $folder !== $request->getStage('module')) {
            continue;
        }

        $query = file_get_contents($file);

        CommandLine::info('Populating ' . $folder);

        try {
            $database->query($query);
        } catch(SqlException $e) {
            CommandLine::error($e->getMessage());
        }
    }
};
