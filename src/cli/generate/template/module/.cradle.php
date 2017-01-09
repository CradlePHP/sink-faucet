<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\{{namespace}}\Service as {{camel name 1}}Service;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('{{name}}', {{camel name 1}}Service::class);
