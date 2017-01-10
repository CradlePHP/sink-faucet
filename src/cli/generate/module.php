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

    CommandLine::system('Generating module...');
    $handlebars = include __DIR__ . '/helper/handlebars.php';

    //get source and destination root
    $destinationRoot = $cwd . '/module/' . $active;
    $sourceRoot = __DIR__ . '/template/module';

    $init = [];
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
            // if /template/module/src/foo/bar/events.php, then /src/foo/bar/events.php
            // if /template/module/.cradle.php, then /.cradle.php
            $path = substr($source->getPathname(), strlen($sourceRoot));
            $destination = $destinationRoot . $path;

            //for multi modules
            if(strpos($data['namespace'], '\\') !== false) {
                if(strpos($path, '/src') === 0) {
                    // /src/foo/bar/events.php to /foo/bar/events.php
                    $suffix = substr($path, 4);
                    //Oauth\Post to Post
                    $prefix = substr($data['namespace'], strpos($data['namespace'], '\\') + 1);
                    // /src/Post/foo/bar/events.php
                    $destination = $destinationRoot . '/src/' . $prefix . $suffix;
                } else if(strpos($path, '/test') === 0) {
                    // /test/foo/bar/events.php to /foo/bar/events.php
                    $suffix = substr($path, 5);
                    //Oauth\Post to Post
                    $prefix = substr($data['namespace'], strpos($data['namespace'], '\\') + 1);
                    // /src/Post/foo/bar/events.php
                    $destination = $destinationRoot . '/test/' . $prefix . $suffix;
                } else if($path === '/.cradle.php') {
                    $init[] = [
                        'name' => $data['name'],
                        'capital' => ucwords($data['name']),
                        'namespace' => $data['namespace']
                    ];
                    //Skipping
                    continue;
                }
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

            //does it not exist?
            if(!is_dir(dirname($destination))) {
                //then make it
                mkdir(dirname($destination), 0777, true);
            }

            $contents = file_get_contents($source->getPathname());
            $contents = str_replace('\\', '\\\\', $contents);
            $template = $handlebars->compile($contents);

            $contents = $template($data);
            $contents = str_replace('{{ ', '{{', $contents);

            file_put_contents($destination, $contents);
        }
    }

    //for multi modules
    if(!empty($init)) {
        $contents = ['<?php //-->'];
        $templates = [
            'include' => 'include_once __DIR__ . \'/src/%s/events.php\';',
            'namespace' => 'use Cradle\\Module\\%s\\Service as %sService;',
            'register' => 'ServiceFactory::register(\'%s\', %sService::class);'
        ];


        foreach($init as $item) {
            $contents[] = sprintf($templates['include'], $item['capital']);
        }

        $contents[] = '';
        foreach($init as $item) {
            $contents[] = sprintf($templates['namespace'], $item['namespace'], $item['capital']);
        }

        $contents[] = 'use Cradle\Module\Utility\ServiceFactory;';
        $contents[] = '';
        foreach($init as $item) {
            $contents[] = sprintf($templates['register'], $item['name'], $item['capital']);
        }

        $contents[] = '';

        $destination = $destinationRoot . '/.cradle.php';

        CommandLine::info('Making ' . $destination);

        //does it not exist?
        if(!is_dir(dirname($destination))) {
            //then make it
            mkdir(dirname($destination), 0777, true);
        }

        file_put_contents($destination, implode("\n", $contents));
    }

    //add to composer.json
    $composerFile = $cwd . '/composer.json';
    if(file_exists($composerFile)) {
        $camel = str_replace(['-', '_'], ' ', $active);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        $flag = '"psr-4": {';
        $add = '"Cradle\\\\Module\\\\' . $camel . '\\\\": "module/' . $active . '/src/",';

        $contents = file_get_contents($composerFile);
        if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
            $contents = str_replace($flag, $flag . "\n            " . $add, $contents);
        }

        CommandLine::info('Updating ' . $composerFile);
        file_put_contents($composerFile, $contents);
    }

    //add to bootstrap.php
    $bootstrapFile = $cwd . '/bootstrap.php';
    if(file_exists($bootstrapFile)) {
        $flag = '->register(\'/module/utility\');';
        $add = '->register(\'/module/' . $active . '\')';

        $contents = file_get_contents($bootstrapFile);
        if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
            $contents = str_replace($flag, $add . "\n    " . $flag, $contents);
        }

        CommandLine::info('Updating ' . $bootstrapFile);
        file_put_contents($bootstrapFile, $contents);
    }

    //add to phpunit.xml
    $phpunitFile = $cwd . '/phpunit.xml';
    if(file_exists($phpunitFile)) {
        $flag = '</testsuites>';
        $add = "\t".'<testsuite name="'. ucwords($active) .' Test Suite">';
        
        if(empty($init)) {
            $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/ValidatorTest.php</file>';
            $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/ServiceTest.php</file>';
            $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/Service/SqlServiceTest.php</file>';
            $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/Service/ElasticServiceTest.php</file>';
            $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/EventTest.php</file>';
        } else {
            foreach ($init as $item) {
                $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/'. $item['capital'] .'/ValidatorTest.php</file>';
                $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/'. $item['capital'] .'/ServiceTest.php</file>';
                $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/'. $item['capital'] .'/Service/SqlServiceTest.php</file>';
                $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/'. $item['capital'] .'/Service/ElasticServiceTest.php</file>';
                $add .= PHP_EOL."\t\t\t".'<file>module/'. $active .'/test/'. $item['capital'] .'/EventTest.php</file>';
            }    
        }

        $add .= PHP_EOL."\t\t".'</testsuite>';

        $contents = file_get_contents($phpunitFile);

        if(strpos($contents, $flag) !== false && strpos($contents, $add) === false) {
            $contents = str_replace($flag, $add. "\n\t" .$flag, $contents);
        }

        CommandLine::info('Updating ' . $phpunitFile);
        file_put_contents($phpunitFile, $contents);
    }

    CommandLine::success($active . ' module was generated. Run `composer update`.');
};
