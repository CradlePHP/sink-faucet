<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\CommandLine\Index as CommandLine;
use Cradle\Sink\Faucet\Installer;

/**
 * CLI project update
 *
 * @param Request $request
 * @param Response $response
 */
return function ($request, $response) {
    //setup the configs
    CommandLine::system('Updating project...');

    $versions = Installer::install();

    foreach($versions as $path => $version) {
        CommandLine::success('Updated ' . $path . ' to v' . $version);
    }
};
