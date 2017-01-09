<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\{{namespace}}\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  {{capital name}}
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_{{classspace}}_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\{{namespace}}\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        {{~#each fields}}{{~#each validation}}
        {{~#when method '===' 'required'}}
        $this->assertEquals('{{message}}', $actual['{{../@key}}']);
        {{~/when}}{{/each}}{{/each}}
    }

    /**
     * @covers Cradle\Module\{{namespace}}\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['{{primary}}']);
    }
}
