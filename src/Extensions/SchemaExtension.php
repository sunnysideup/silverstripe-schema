<?php

/**
 * SchemaExtension.php
 *
 * @author Bram de Leeuw
 * Date: 03/11/16
 */

namespace Broarm\Schema;

use Broarm\Schema\SchemaBuilder;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

/**
 * SchemaExtension
 */
class SchemaExtension extends DataExtension
{
    private static $exempted_get_vars = [
        'start',
        'flush',
    ];

    /**
     * Hook onto the page meta tags and append any configured schema objects
     * fixme: does not trigger correctly on DataObjects pages
     *
     * @param $tags
     */
    public function MetaTags(&$tags)
    {
        $curr = Controller::curr();
        if ($curr) {
            $request = Controller::curr()->getRequest();
            if ($request) {
                if ($request->isAjax()) {
                    return;
                }
                if ($request->param('Action')) {
                    return;
                }
                $postVars = $request->postVars();
                if (! empty($postVars)) {
                    return;
                }
                $getVars = $request->getVars();
                if (! empty($getVars)) {
                    if (count($_GET) > 1) {
                        return;
                    } else {
                        if (!empty(array_intersect(array_keys($getVars), Config::inst()->get(static::class, 'exempted_get_vars')))) {
                            return;
                        }
                    }
                }
                $schemaBuilders = $this->getSchemasOrg();
                /** @var SchemaBuilder $schemaBuilder */
                foreach ($schemaBuilders as $schemaBuilder) {
                    $this->appendSchemaOrg($tags, $schemaBuilder);
                }
            }
        }
    }


    /**
     * Append a schema ld+json tag
     *
     * @param $tags
     * @param $schema
     */
    private function appendSchemaOrg(&$tags, $schemaBuilder)
    {
        if ($schemaBuilder) {
            $owner = $this->getOwner();
            $array = $schemaBuilder->getSchemaFromCache($owner);
            if (!empty($array)) {
                $string = str_replace('$', '&#36;', json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                Requirements::insertHeadTags(
                    '<script type="application/ld+json">' . $string . '</script>',
                    get_class($schemaBuilder)
                );
            }
        }
    }

    /**
     *
     *
     * @return SchemaBuilder[]
     */
    public function getSchemasOrg()
    {
        $array = [];
        $schemas = array_filter($this->owner->config()->get('active_schema'));
        foreach ($schemas as $schema) {
            if (class_exists($schema)) {
                $schemaBuilder = new $schema();
                if ($schemaBuilder instanceof SchemaBuilder) {
                    $array[$schema] = $schemaBuilder;
                }
            }
        }
        return $array;
    }

    public function onAfterWrite()
    {
        $owner = $this->getOwner();
        if (! $owner->hasExtension(Versioned::class)) {
            SchemaBuilder::clear_schema_cache();
        }
    }

    public function onAfterPublish()
    {
        SchemaBuilder::clear_schema_cache();
    }

    public function updateCMSFields(FieldList $fields)
    {

        $fields->addFieldsToTab(
            'Root.Schema',
            [
                LiteralField::create(
                    'SchemaDotOrgTestLinkNice',
                    '<p><a href="' . $this->getSchemaTestLink() . '" target="_blank" rel="noopener noreferrer">Review Schema for ' . $this->getOwner()->Title . '</a></p>'
                ),
                LiteralField::create(
                    'SchemaDotOrgPrintOutTypes',
                    '<h2>Schema Types</h2><pre>' . json_encode($this->getSchemasOrg(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>'
                ),
                LiteralField::create(
                    'SchemaDotOrgPrintOutDetails',
                    '<h2>Schema Details</h2><pre>' . json_encode($this->getSchemaOrgTestData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>'
                ),
            ]
        );
    }

    public function getSchemaTestLink(): string
    {
        return 'https://validator.schema.org/#' . urlencode($this->getOwner()->AbsoluteLink());

    }

    protected function getSchemaOrgTestData() : array
    {
        $owner = $this->getOwner();
        $all = [];
        $schemaBuilders = $this->getSchemasOrg();
        /** @var SchemaBuilder $schemaBuilder */
        foreach ($schemaBuilders as $schemaBuilder) {
            if ($schemaBuilder) {
                $array = $schemaBuilder->getSchema($owner);
                if (!empty($array)) {
                    $all[] = $array;

                }
            }
        }
        return $all;
    }

    protected function todo()
    {

    }


}


