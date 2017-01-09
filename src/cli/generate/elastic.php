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
    $cwd = $request->getServer('PWD');

    $schemaRoot = $cwd . '/schema';
    if(!is_dir($schemaRoot)) {
        return CommandLine::error('Schema folder not found. Generator Aborted.');
    }

    //Available schemas
    $schemas = [];
    $paths = scandir($schemaRoot, 0);
    foreach($paths as $path) {
        if($path === '.' || $path === '..' || substr($path, -4) !== '.php') {
            continue;
        }

        $schemas[] = pathinfo($path, PATHINFO_FILENAME);
    }

    if(empty($schemas)) {
        return CommandLine::error('No schemas found in ' . $schemaRoot);
    }

    //determine the schema
    $schemaName = $request->getStage('schema');

    if(!$schemaName) {
        CommandLine::info('Available schemas:');
        foreach($schemas as $schema) {
            CommandLine::info(' - ' . $schema);
        }

        $schemaName = CommandLine::input('Which schema to use?');
    }

    if(!in_array($schemaName, $schemas)) {
        return CommandLine::error('Invalid schema. Generator Aborted.');
    }

    $schema = $schemaRoot . '/' . $schemaName . '.php';

    if(!file_exists($schema)) {
        return CommandLine::error($schema . ' not found. Aborting.');
    }

    CommandLine::system('Generating Elastic Map...');

    //get the template data
    $data = (new Schema($schemaRoot, $schemaName))->getData();

    $map = [];
    foreach($data['fields'] as $name => $field) {
        if(isset($field['elastic'])) {
            $map[$data['name']][$name] = $field['elastic'];
        }
    }

    foreach($data['relations'] as $relation) {
        if($relation['many']) {
            continue;
        }

        foreach($relation['fields'] as $name => $field) {
            if(isset($field['elastic'])) {
                $map[$data['name']][$name] = $field['elastic'];
            }
        }
    }

    //get destination
    $destination = $cwd . '/module/' . $schemaName . '/elastic.php';

    //if the destination exists
    if(file_exists($destination)) {
        //ask questions
        $overwrite = CommandLine::input($destination .' exists. Overwrite?(n)', 'n');
        if($overwrite === 'n') {
            CommandLine::error('Aborting...');
            return;
        }
    }

    if(!is_dir($cwd . '/module/' . $schemaName)) {
        mkdir(dirname($cwd . '/module/' . $schemaName), 0777, true);
    }

    $contents = '<?php return ' . var_export($map, true) . ';';
    file_put_contents($destination, $contents);

    CommandLine::success('Elastic map generated.');
    CommandLine::info('Recommended actions:');
    CommandLine::info(' - bin/cradle faucet map-elastic');
};
