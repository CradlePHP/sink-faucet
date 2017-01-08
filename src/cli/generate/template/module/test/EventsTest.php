<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  {{capital name}}
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_{{camel name 1}}_EventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Request $response
     */
    protected $response;

    /**
     * @var int $id
     */
    protected static $id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->request->load();
        $this->response->load();
    }

    /**
     * {{name}}-create
     *
     * @covers Cradle\Module\{{camel name 1}}\Validator::getCreateErrors
     * @covers Cradle\Module\{{camel name 1}}\Validator::getOptionalErrors
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function test{{camel name 1}}Create()
    {
        $this->request->setStage([
            {{~#each fields}}{{~#each validation}}
            {{~#when method '===' 'required'}}
            '{{../@key}}' => {{../test.pass}},
            {{~/when}}{{/each}}{{/each}}
            {{~#each relations}}{{#unless many}}
            '{{primary}}' => 1,
            {{~/unless}}{{/each}}
        ]);

        cradle()->trigger('{{name}}-create', $this->request, $this->response);

        {{~#each fields}}{{~#each validation}}
        {{~#when method '===' 'required'}}
        $this->assertEquals({{../test.pass}}, $this->response->getResults('{{../@key}}'));
        {{~/when}}{{/each}}{{/each}}
        self::$id = $this->response->getResults('{{primary}}');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * {{name}}-detail
     *
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function test{{camel name 1}}Detail()
    {
        $this->request->setStage('{{primary}}', 1);

        cradle()->trigger('{{name}}-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('{{primary}}'));
    }

    /**
     * {{name}}-remove
     *
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function test{{camel name 1}}Remove()
    {
        $this->request->setStage('{{primary}}', self::$id);

        cradle()->trigger('{{name}}-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('{{primary}}'));
    }

    {{~#if active}}

    /**
     * {{name}}-restore
     *
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function test{{camel name 1}}Restore()
    {
        $this->request->setStage('{{primary}}', 581);

        cradle()->trigger('{{name}}-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('{{primary}}'));
        $this->assertEquals(1, $this->response->getResults('{{active}}'));
    }
    {{~/if}}

    /**
     * {{name}}-search
     *
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::search
     * @covers Cradle\Module\{{camel name 1}}\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function test{{camel name 1}}Search()
    {
        cradle()->trigger('{{name}}-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, '{{primary}}'));
    }

    /**
     * {{name}}-update
     *
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\{{camel name 1}}\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function test{{camel name 1}}Update()
    {
        $this->request->setStage([
            '{{primary}}' => self::$id,
            {{~#each fields}}{{~#each validation}}
            {{~#when method '===' 'required'}}
            '{{../@key}}' => {{../test.pass}},
            {{~/when}}{{/each}}{{/each}}
            {{~#each relations}}{{#unless many}}
            '{{primary}}' => 1,
            {{~/unless}}{{/each}}
        ]);

        cradle()->trigger('{{name}}-update', $this->request, $this->response);

        {{~#each fields}}{{~#each validation}}
        {{~#when method '===' 'required'}}
        $this->assertEquals({{../test.pass}}, $this->response->getResults('{{../@key}}'));
        {{~/when}}{{/each}}{{/each}}
        $this->assertEquals(self::$id, $this->response->getResults('{{primary}}'));
    }
}
