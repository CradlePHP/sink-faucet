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
        if($path === '.' || $path === '..') {
            continue;
        }

        //if multiple
        if(substr($path, -4) !== '.php') {
            $subPath = scandir($schemaRoot .'/'. $path, 0);
            $subPath = array_values(array_diff($subPath,array('.', '..')));

            if(!empty($subPath)) {
                $schemas[$path] = array_values($subPath);
                continue;
            }
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

        foreach($schemas as $key => $schema) {
            if(is_array($schema)) {
                CommandLine::info(' - ' . $key);
                continue;
            }

            CommandLine::info(' - ' . $schema);
        }

        $schemaName = CommandLine::input('Which schema to use?');
    }

    if(!in_array($schemaName, $schemas) && !array_key_exists($schemaName, $schemas)) {
        return CommandLine::error('Invalid schema. Generator Aborted.');
    }

    if(array_key_exists($schemaName, $schemas)) {
        $isMulti = true;
    }

    //if multiple module
    if($isMulti) {
        foreach ($schemas[$schemaName] as $subSchema) {
            $schema = $schemaRoot . '/' . $schemaName . '/' . $subSchema;

            $subSchema = str_replace('.php', '', $subSchema);
            if(!file_exists($schema)) {
                return CommandLine::error($subSchema . ' not found. Aborting.');
            }

            CommandLine::system('Generating module...');

            //get the template data
            $handlebars = include __DIR__ . '/helper/handlebars.php';
            $data = (new Schema($schemaRoot.'/'.$schemaName, $subSchema))->getData();

            //get destination root
            $destinationRoot = $cwd . '/module/' . $schemaName;

            //get all the files
            $sourceRoot = __DIR__ . '/template/multi-module';
            $paths = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot));
            foreach ($paths as $source) {
                //is it a folder ?
                if($source->isDir()) {
                    continue;
                }

                //it's a file, determine the destination
                // if /template/module/src/events.php, then /path/to/file
                $destination = $destinationRoot . substr($source->getPathname(), strlen($sourceRoot));
                $destination = str_replace('NAME', ucwords($subSchema), $destination);

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
                $contents = str_replace('\\', '\\\\', $contents);
                $template = $handlebars->compile($contents);

                $contents = $template($data);
                $contents = str_replace('{{ ', '{{', $contents);

                file_put_contents($destination, $contents);
            }

            //add to cradle.php
            $cradleFile = $cwd . '/module/'. $schemaName .'/.cradle.php';

            if(!file_exists($cradleFile)) {
                $content = '<?php ';
                $content .= PHP_EOL.'//include'.PHP_EOL;
                $content .= PHP_EOL.'//use'.PHP_EOL;
                $content .= PHP_EOL.'use Cradle\Module\Utility\ServiceFactory;'.PHP_EOL;
                $content .= PHP_EOL.'//service';
                file_put_contents($cradleFile, $content);
            }

            if(file_exists($cradleFile)) {
                //include append
                $flag = '//include';
                $add = 'include_once __DIR__ . \'/src/' .ucwords($subSchema). '/events.php\';';

                $contents = file_get_contents($cradleFile);
                if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                    $contents = str_replace($flag, $flag . PHP_EOL . $add, $contents);
                }

                file_put_contents($cradleFile, $contents);


                //use append
                $flag = '//use';
                $add = 'use Cradle\Module\\' .ucwords($schemaName). '\\' .ucwords($subSchema). '\\Service as ' .ucwords($subSchema). 'Service;';

                if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                    $contents = str_replace($flag, $flag . PHP_EOL . $add, $contents);
                }

                file_put_contents($cradleFile, $contents);

                //service append
                $flag = '//service';
                $add = 'ServiceFactory::register(\''. $subSchema .'\', '. ucwords($subSchema) .'Service::class);';

                if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                    $contents = str_replace($flag, $flag . PHP_EOL . $add, $contents);
                }

                file_put_contents($cradleFile, $contents);
            }

            //add to composer.json
            $composerFile = $cwd . '/composer.json';
            if(file_exists($composerFile)) {
                $camel = str_replace(['-', '_'], ' ', $schemaName);
                $camel = ucwords($camel);
                $camel = str_replace(' ', '', $camel);
                $flag = '"psr-4": {';
                $add = '"Cradle\\\\Module\\\\' . $camel . '\\\\": "module/' . $schemaName . '/src/",';

                $contents = file_get_contents($composerFile);
                if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                    $contents = str_replace($flag, $flag . "\n            " . $add, $contents);
                }

                file_put_contents($composerFile, $contents);
            }

            //add to bootstrap.php
            $bootstrapFile = $cwd . '/bootstrap.php';
            if(file_exists($bootstrapFile)) {
                $flag = '->register(\'/module/utility\');';
                $add = '->register(\'/module/' . $schemaName . '\')';

                $contents = file_get_contents($bootstrapFile);
                if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
                    $contents = str_replace($flag, $add . "\n    " . $flag, $contents);
                }

                file_put_contents($bootstrapFile, $contents);
            }
        }

        return;
    }

    //else single module
    $schema = $schemaRoot . '/' . $schemaName . '.php';

    if(!file_exists($schema)) {
        return CommandLine::error($schema . ' not found. Aborting.');
    }

    CommandLine::system('Generating module...');

    //get the template data
    $handlebars = include __DIR__ . '/helper/handlebars.php';
    $data = (new Schema($schemaRoot, $schemaName))->getData();

    //get destination root
    $destinationRoot = $cwd . '/module/' . $schemaName;

    //get all the files
    $sourceRoot = __DIR__ . '/template/module';
    $paths = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot));
    foreach ($paths as $source) {
        //is it a folder ?
        if($source->isDir()) {
            continue;
        }

        //it's a file, determine the destination
        // if /template/module/src/events.php, then /path/to/file
        $destination = $destinationRoot . substr($source->getPathname(), strlen($sourceRoot));

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
        $contents = str_replace('\\', '\\\\', $contents);
        $template = $handlebars->compile($contents);

        $contents = $template($data);
        $contents = str_replace('{{ ', '{{', $contents);

        file_put_contents($destination, $contents);
    }

    //add to composer.json
    $composerFile = $cwd . '/composer.json';
    if(file_exists($composerFile)) {
        $camel = str_replace(['-', '_'], ' ', $data['name']);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        $flag = '"psr-4": {';
        $add = '"Cradle\\\\Module\\\\' . $camel . '\\\\": "module/' . $data['name'] . '/src/",';

        $contents = file_get_contents($composerFile);
        if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
            $contents = str_replace($flag, $flag . "\n            " . $add, $contents);
        }

        file_put_contents($composerFile, $contents);
    }

    //add to bootstrap.php
    $bootstrapFile = $cwd . '/bootstrap.php';
    if(file_exists($bootstrapFile)) {
        $flag = '->register(\'/module/utility\');';
        $add = '->register(\'/module/' . $data['name'] . '\')';

        $contents = file_get_contents($bootstrapFile);
        if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
            $contents = str_replace($flag, $add . "\n    " . $flag, $contents);
        }

        file_put_contents($bootstrapFile, $contents);
    }

    CommandLine::success($schemaName . ' module was generated. Run `composer update`.');
};
