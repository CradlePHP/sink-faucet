<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * CLI starts worker
 *
 * @param Request $request
 * @param Response $response
 */
return function ($request, $response) {
    //get the queue name
    $name = 'queue';
    if($request->hasStage(0)) {
        $name = $request->getStage(0);
    } else if($request->hasStage('name')) {
        $name = $request->getStage('name');
    }

    $verbose = false;
    if($request->hasStage('v') || $request->hasStage('verbose')) {
        $verbose = true;
        cradle()->addLogger(function($message) {
            echo $message . PHP_EOL;
        });
    }

    $mode = 'work';
    if($request->hasStage('m')) {
        $mode = $request->getStage('m');
    } else if($request->hasStage('mode')) {
        $mode = $request->getStage('mode');
    }

    switch($mode) {
        case 'fork':
            $mode = 'workFork';
            break;
        case 'exec':
            $mode = 'workExec';
            break;
    }

    cradle('global')->$mode($name, '[cradle]', $verbose);
};
