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

    CommandLine::system('Generating REST...');
    $handlebars = include __DIR__ . '/helper/handlebars.php';

    //get source and destination root
    $sourceRoot = __DIR__ . '/template/rest';
    $destinationRoot = $cwd . '/app/api/src/controller/rest';

    foreach($schemas as $schema) {
        //get the template data
        $data = (new Schema($schemaRoot, $schema))->getData();

        //get all the files
        $paths = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot));
        foreach ($paths as $source) {
            //is it a folder ?
            if($source->isDir()) {
                continue;
            }

            //it's a file, determine the destination
            // if /template/module/src/events.php, then /path/to/file
            $destination = $destinationRoot . substr($source->getPathname(), strlen($sourceRoot));
            $destination = str_replace('NAME', $data['name'], $destination);

            //does it not exist?
            if(!is_dir(dirname($destination))) {
                //then make it
                mkdir(dirname($destination), 0777, true);
            }

            //if the destination exists
            if(file_exists($destination)) {
                //ask questions
                $overwrite = CommandLine::input($destination .' exists. Overwrite?(n)', 'n');
                if($overwrite === 'n') {
                    CommandLine::warning('Skipping...');
                    continue;
                }
            }

            CommandLine::info('Making ' . $destination);

            $contents = file_get_contents($source->getPathname());
            $template = $handlebars->compile($contents);

            $contents = $template($data);
            $contents = str_replace('{{ ', '{{', $contents);

            file_put_contents($destination, $contents);
        }

        //add to cradle.php
        $cradleFile = $cwd . '/app/api/.cradle.php';
        if(file_exists($cwd . '/app/api/.cradle')) {
            $cradleFile = $cwd . '/app/api/.cradle';
        }

        if(file_exists($cradleFile)) {
            $flag = '//START: GENERATED CONTROLLERS';
            $add = 'include_once __DIR__ . \'/src/controller/rest/' . $data['name'] . '.php\';';

            $contents = file_get_contents($cradleFile);
            if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                $contents = str_replace($flag, $flag . PHP_EOL . $add, $contents);
            }

            CommandLine::info('Updating ' . $cradleFile);
            file_put_contents($cradleFile, $contents);
        }
    }

    CommandLine::success($active . ' REST was generated.');
};
