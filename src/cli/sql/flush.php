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

/**
 * CLI clear index
 *
 * @param Request $request
 * @param Response $response
 */
return function ($request, $response) {
    CommandLine::system('Flushing SQL...');

    $database = SqlFactory::load($this->package('global')->service('sql-main'));

    //truncate all tables
    $tables = $database->getTables();
    foreach ($tables as $table) {
        //if we just want to flush one table
        if($request->hasStage('table') && $table === $request->getStage('table')) {
            continue;
        }

        //if we just want to flush one tableset
        if($request->hasStage('tableset') && strpos($table, $request->getStage('tableset')) !== 0) {
            continue;
        }

        $database->query('TRUNCATE TABLE `' . $table . '`;');
    }
};
