<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\{{namespace}}\Service;

/**
 * SQL service test
 * {{capital name}} Model Test
 *
 * @vendor   Acme
 * @package  {{capital name}}
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_{{classspace}}_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            {{~#each fields}}{{~#each validation}}
            {{~#when method '===' 'required'}}
            '{{../@key}}' => {{../test.pass}},
            {{~/when}}{{/each}}{{/each}}
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['{{primary}}']);
    }

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['{{primary}}']);
    }

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['{{primary}}']);
    }

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            '{{primary}}' => $id,
            {{~#each fields}}{{~#each validation}}
            {{~#when method '===' 'required'}}
            '{{../@key}}' => {{../test.pass}},
            {{~/when}}{{/each}}{{/each}}
        ]);

        $this->assertEquals($id, $actual['{{primary}}']);
    }
    {{~#if unique.0}}

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::exists
     */
    public function testExists()
    { {{#each fields}}{{#if sql.unique}}
        $actual = $this->object->exists({{test.pass}});
        {{~/if}}{{/each}}
        // it returns a boolean so we're expecting it to be true because
        // the slug provided is saved in the database
        $this->assertTrue($actual);
    }
    {{/if}}

    /**
     * @covers Cradle\Module\{{namespace}}\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['{{primary}}']);
    }

    {{~#each relations}}

    /**
     * @covers Cradle\Module\{{camel ../name 1}}\Service\SqlService::link{{camel name 1}}
     */
    public function testLink{{camel name 1}}()
    {
        $actual = $this->object->link{{camel name 1}}(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['{{../primary}}']);
        $this->assertEquals(999, $actual['{{primary}}']);
    }

    /**
     * @covers Cradle\Module\{{camel ../name 1}}\Service\SqlService::unlink{{camel name 1}}
     */
    public function testUnlink{{camel name 1}}()
    {
        $actual = $this->object->unlink{{camel name 1}}(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['{{../primary}}']);
        $this->assertEquals(999, $actual['{{primary}}']);
    }

        {{~#if many}}

    /**
     * @covers Cradle\Module\{{camel ../name 1}}\Service\SqlService::unlink{{camel name 1}}
     */
    public function testUnlinkAll{{camel name 1}}()
    {
        $actual = $this->object->unlinkAll{{camel name 1}}(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['{{../primary}}']);
    }
        {{~/if}}
    {{/each}}
}
