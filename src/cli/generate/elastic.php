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

    CommandLine::system('Generating Elastic Map...');

    $map = [];
    foreach($schemas as $schema) {
        //get the template data
        $data = (new Schema($schemaRoot, $schema))->getData();

        if(isset($data['primary'])) {
            $map[$data['name']][$data['primary']] = ['type' => 'integer'];
        }

        if(isset($data['active'])) {
            $map[$data['name']][$data['active']] = ['type' => 'short'];
        }

        if(isset($data['created'])) {
            $map[$data['name']][$data['created']] = [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ];
        }

        if(isset($data['updated'])) {
            $map[$data['name']][$data['updated']] = [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ];
        }

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
    }

    //get destination
    $destination = $cwd . '/module/' . $active . '/elastic.php';

    //if the destination exists
    if(file_exists($destination)) {
        //ask questions
        $overwrite = CommandLine::input($destination .' exists. Overwrite?(n)', 'n');
        if($overwrite === 'n') {
            CommandLine::error('Aborting...');
            return;
        }
    }

    if(!is_dir($cwd . '/module/' . $active)) {
        mkdir(dirname($cwd . '/module/' . $active), 0777, true);
    }

    $contents = '<?php return ' . var_export($map, true) . ';';
    file_put_contents($destination, $contents);

    CommandLine::success('Elastic map generated.');
    CommandLine::info('Recommended actions:');
    CommandLine::info(' - bin/cradle faucet map-elastic');
};
