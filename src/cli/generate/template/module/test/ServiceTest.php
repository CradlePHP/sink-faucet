<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\{{namespace}}\Service;

use Cradle\Module\{{namespace}}\Service\SqlService;
use Cradle\Module\{{namespace}}\Service\RedisService;
use Cradle\Module\{{namespace}}\Service\ElasticService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  {{capital name}}
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_{{classspace}}_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\{{namespace}}\Service::get
     */
    public function testGet()
    {
        $this->assertInstanceOf(SqlService::class, Service::get('sql'));
        $this->assertInstanceOf(RedisService::class, Service::get('redis'));
        $this->assertInstanceOf(ElasticService::class, Service::get('elastic'));
    }
}
