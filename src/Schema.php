<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Sink\Faucet;

/**
 * Schema
 *
 * Sample field:
 * [
 *      'sql' => [
 *         'type' => 'varchar',
 *         'length' => 255,
 *         'attributes' => 'unsigned',
 *         'default' => 'Foobar',
 *         'comment' => 'foobar',
 *         'required' => true,
 *         'key' => true,
 *         'unique' => true,
 *         'primary' => true,
 *         'encoding' => false
 *     ],
 *     'elastic' => [
 *         'type' => 'string',
 *         'fields' => [
 *             'keyword' => [
 *                 'type' => 'keyword'
 *             ]
 *         ]
 *     ],
 *     'form' => [
 *         'label' => 'Text Example',
 *         'type' => false,
 *         'default' => 'foobar',
 *         'attributes' => [
 *             'placeholder' => 'Sample Text',
 *         ],
 *         'options' => [
 *             '' => 'Choose one',
 *             'choice1' => 'Choice 1',
 *             'choice2' => 'Choice 2',
 *         ],
 *         'scripts' => []
 *     ],
 *     'list' => [
 *         'label' => 'Text',
 *         'searchable' => true,
 *         'sortable' => true,
 *         'filterable' => true,
 *         'format' => length,
 *         'parameters' => 255
 *     ],
 *     'detail' => [
 *         'label' => 'Text',
 *         'format' => 'date',
 *         'parameters' => 'Y-m-d H:i:s'
 *     ],
 *     'validation' => [
 *         [
 *             'method' => 'required',
 *             'message' => 'Is required',
 *             'parameters' => []
 *         ]
 *     ],
 *     'test' => [
 *         'pass' => 'foo',
 *         'fail' => 'bar'
 *     ]
 * ],
 *
 * SQL Encoding Options
 * - md5
 * - sha1
 * - uuid
 * - token
 * - datetime
 * - date
 * - time
 * - created
 * - updated
 * - json
 * - bool
 * - [inline]
 *
 * Form Type Options
 * - input
 * - select
 * - textarea
 * - radio
 * - radios
 * - checkbox
 * - checkboxes
 * - button
 * - [inline]
 *
 * List and Detail Format Options
 * - date
 * - length
 * - words
 * - link
 * - image
 * - email
 * - phone
 * - capital
 * - implode
 * - upper
 * - lower
 * - [inline]
 *
 * Validation Method Options
 * - required
 * - empty
 * - one
 * - number
 * - gt
 * - lt
 * - char_eq
 * - char_gt
 * - char_lt
 * - word_eq
 * - word_gt
 * - word_lt
 * - regexp
 * - unique
 * - [inline]
 * @vendor   Cradle
 * @package  Faucet
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Schema
{
    /**
     * @var string $root
     */
    protected $root;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $schema
     */
    protected $schema;

    /**
     * Sets the schema name and path
     *
     * @param *string $root Location to the schema folder
     * @param *string $name can be `post` or `ecommerce/product`
     */
    public function __construct($root, $name)
    {
        $this->root = $root;
        $this->name = $name;

        if(strpos($name, '/') !== false) {
            $this->name = substr($name, strpos($name, '/') + 1);
        }

        $this->scehma = $this->root . '/' . $name . '.php';
    }

    public function getData()
    {
        if(!file_exists($this->scehma)) {
            return false;
        }

        $data = include $this->scehma;
        $data['name'] = $this->name;

        if(!isset($data['fields']) || !is_array($data['fields'])) {
            $data['fields'] = [];
        }

        foreach($data['fields'] as $name => $field) {
            $data['fields'][$name] = $this->normalizeField($name, $field);
            $this->addFlags($data['fields'][$name], $data);
        }

        if(!isset($data['relations']) || !is_array($data['relations'])) {
            $data['relations'] = [];
        }

        foreach($data['relations'] as $name => $relation) {
            $data['relations'][$name]['name'] = $name;

            if(strpos($name, '/') !== false) {
                $data['relations'][$name]['name'] = substr($name, strpos($name, '/') + 1);
            }

            //prevent recursion loop
            if($this->scehma === $this->root . '/' . $name . '.php') {
                $schema = $data;
            } else {
                $schema = new self($this->root, $name);
                $schema = $schema->getData();
            }

            if(!$schema) {
                continue;
            }

            if(!isset($relation['primary']) && isset($schema['primary'])) {
                $data['relations'][$name]['primary'] = $schema['primary'];
            }

            if($relation['many']) {
                continue;
            }

            foreach($schema['fields'] as $name => $field) {
                if(isset($field['sql']['type']) && $field['sql']['type'] === 'json') {
                    $data['json'][] = $name;
                }
            }
        }

        return $data;
    }

    protected function addFlags($field, &$data)
    {
        if(isset($field['sql']['unique']) && $field['sql']['unique']) {
            $data['unique'][] = $field['name'];
        }

        if(isset($field['sql']['type']) && $field['sql']['type'] === 'json') {
            $data['json'][] = $field['name'];
        }

        if(isset($field['sql']['searchable']) && $field['sql']['searchable']) {
            $data['searchable'][] = $field['name'];
        }

        if(isset($field['sql']['sortable']) && $field['sql']['sortable']) {
            $data['sortable'][] = $field['name'];
        }

        if(isset($field['sql']['filterable']) && $field['sql']['filterable']) {
            $data['filterable'][] = $field['name'];
        }

        if(isset($field['field']['default'])) {
            $data['defaults'][$field['name']] = $field['field']['default'];
        }

        if(isset($field['form']['type'])
            && (
                $field['form']['type'] === 'file'
                || $field['form']['type'] === 'image'
            )
        )
        {
            $data['has_file'] = true;
        } else if(isset($field['form']['inline_type'])
            && (
                $field['form']['inline_type'] === 'image-field'
                || $field['form']['inline_type'] === 'images-field'
            )
        )
        {
            $data['has_file'] = true;
        }
    }

    protected function normalizeField($name, $field)
    {
        $field['name'] = $name;

        //auto set the encoding
        if(isset($field['sql']['type']) && !isset($field['sql']['encoding'])) {
            switch($field['sql']['type']) {
                case 'datetime':
                case 'date':
                case 'time':
                case 'json':
                case 'bool':
                    $field['sql']['encoding'] = $field['sql']['type'];
                    break;
                case 'int':
                    if(isset($field['sql']['length']) && $field['sql']['length'] === 1) {
                        $field['sql']['encoding'] = 'small';
                    }
                    break;
            }
        }

        //default
        if(isset($field['sql']['default'], $field['form'])) {
            $field['form']['default'] = $field['sql']['default'];
        }

        if(isset($field['form']['default'])) {
            if(is_string($field['form']['default'])) {
                $field['form']['default'] = '\''.$field['form']['default'].'\'';
            } else if(is_null($field['form']['default'])) {
                $field['form']['default'] = 'null';
            } else if(is_array($field['form']['default']) || is_object($field['form']['default'])) {
                $field['form']['default'] = var_export($field['form']['default'], true);
            } else if($field['form']['default'] === true) {
                $field['form']['default'] = '1';
            } else if($field['form']['default'] === false) {
                $field['form']['default'] = '0';
            }
        }

        if(isset($field['form']['type'])) {
            switch($field['form']['type']) {
                case 'image':
                    $field['form']['type'] = 'file';
                    $field['form']['attributes']['accept'] = 'image/*';
                case 'file':
                case 'hidden': //sometimes used for JS
                case 'color':
                case 'date':
                case 'email':
                case 'month':
                case 'number':
                case 'password':
                case 'range':
                case 'search':
                case 'tel':
                case 'text':
                case 'time':
                case 'url':
                case 'week':
                    $field['form']['attributes']['type'] = $field['form']['type'];
                    $field['form']['type'] = 'input';
                    break;
            }

            //add bootstrap class
            if(in_array(
                $field['form']['type'],
                    [
                        'input',
                        'select',
                        'textarea'
                    ]
                )
            )
            {
                if(isset($field['form']['attributes']['class'])) {
                    $field['form']['attributes']['class'] .= ' form-control';
                } else {
                    $field['form']['attributes']['class'] = 'form-control';
                }
            }

            //tag
            if($field['form']['type'] === 'tag-field') {
                $code = file_get_contents(__DIR__ . '/cli/generate/template/fields/tags.html');
                $code = str_replace('{NAME}', $field['name'], $code);
                $field['form']['inline_type'] = $field['form']['type'];
                $field['form']['type'] = 'inline';
                $field['form']['code'] = trim($code);
            }

            //image
            if($field['form']['type'] === 'image-field') {
                $code = file_get_contents(__DIR__ . '/cli/generate/template/fields/image.html');
                $code = str_replace('{NAME}', $field['name'], $code);
                $field['form']['inline_type'] = $field['form']['type'];
                $field['form']['type'] = 'inline';
                $field['form']['code'] = trim($code);
            }

            //images
            if($field['form']['type'] === 'images-field') {
                $code = file_get_contents(__DIR__ . '/cli/generate/template/fields/images.html');
                $code = str_replace('{NAME}', $field['name'], $code);
                $field['form']['inline_type'] = $field['form']['type'];
                $field['form']['type'] = 'inline';
                $field['form']['code'] = trim($code);
            }

            //attributes
            if($field['form']['type'] === 'meta-field') {
                $code = file_get_contents(__DIR__ . '/cli/generate/template/fields/meta.html');
                $code = str_replace('{NAME}', $field['name'], $code);
                $field['form']['inline_type'] = $field['form']['type'];
                $field['form']['type'] = 'inline';
                $field['form']['code'] = trim($code);
            }

            //these are all the possible form types
            if(!in_array(
                $field['form']['type'],
                    [
                        'input',
                        'select',
                        'textarea',
                        'radio',
                        'radios',
                        'checkbox',
                        'checkboxes',
                        'button',
                        'inline'
                    ]
                )
            )
            {
                //if not then its inline
                $field['form']['code'] = $field['form']['type'];
                $field['form']['type'] = 'inline';
            }
        }

        //noop to prevent nested if
        if(isset($field['list']) && !isset($field['list']['format'])) {
            $field['list']['format'] = 'noop';
        }

        //these are all the possible list formats
        if(isset($field['list']['format']) && !in_array(
            $field['list']['format'],
                [
                    'date',
                    'length',
                    'words',
                    'link',
                    'image',
                    'email',
                    'phone',
                    'capital',
                    'implode',
                    'upper',
                    'lower',
                    'noop',
                    'inline'
                ]
            )
        )
        {
            //if not then its inline
            $field['list']['code'] = $field['list']['format'];
            $field['list']['format'] = 'inline';
        }

        //noop to prevent nested if
        if(isset($field['detail']) && !isset($field['detail']['format'])) {
            $field['detail']['format'] = 'noop';
        }

        //these are all the possible detail formats
        if(isset($field['detail']['format']) && !in_array(
            $field['detail']['format'],
                [
                    'date',
                    'length',
                    'words',
                    'link',
                    'image',
                    'email',
                    'phone',
                    'capital',
                    'implode',
                    'upper',
                    'lower',
                    'noop',
                    'inline'
                ]
            )
        )
        {
            //if not then its inline
            $field['detail']['code'] = $field['list']['format'];
            $field['detail']['format'] = 'inline';
        }

        if(isset($field['validation'])) {
            foreach($field['validation'] as $validation) {
                if($validation['method'] === 'required') {
                    $field['required'] = true;
                    break;
                }
            }
        }

        if(isset($field['test']['pass'])) {
            if(is_string($field['test']['pass'])) {
                $field['test']['pass'] = '\''.$field['test']['pass'].'\'';
            } else if(is_null($field['test']['pass'])) {
                $field['test']['pass'] = 'null';
            } else if(is_array($field['test']['pass']) || is_object($field['test']['pass'])) {
                $field['test']['pass'] = var_export($field['test']['pass'], true);
            } else if($field['test']['pass'] === true) {
                $field['test']['pass'] = '1';
            } else if($field['test']['pass'] === false) {
                $field['test']['pass'] = '0';
            }
        }

        if(isset($field['test']['fail'])) {
            if(is_string($field['test']['fail'])) {
                $field['test']['fail'] = '\''.$field['test']['fail'].'\'';
            } else if(is_null($field['test']['fail'])) {
                $field['test']['fail'] = 'null';
            } else if(is_array($field['test']['fail']) || is_object($field['test']['fail'])) {
                $field['test']['fail'] = var_export($field['test']['fail'], true);
            } else if($field['test']['fail'] === true) {
                $field['test']['fail'] = '1';
            } else if($field['test']['fail'] === false) {
                $field['test']['fail'] = '0';
            }
        }

        return $field;
    }
}
