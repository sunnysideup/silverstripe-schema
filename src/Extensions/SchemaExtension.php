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
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use Sunnysideup\ArrayToUl\Form\Fields\ExpandableArrayListField;
use Sunnysideup\ArrayToUl\Form\Fields\ExpandableJsonField;
use Sunnysideup\ArrayToUl\View\ExpandableArrayList;

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
    public function getSchemasOrg(): array
    {
        $array = [];
        $schemas = array_filter($this->getOwner()->config()->get('active_schema'));
        foreach ($schemas as $schema) {
            if (class_exists($schema)) {
                $schemaBuilder = new $schema();
                if ($schemaBuilder instanceof SchemaBuilder) {
                    $array[$schema] = $schemaBuilder;
                }
            } else {
                user_error('Schema class ' . $schema . ' does not exist', E_USER_WARNING);
            }
        }
        return $array;
    }

    public function getSchemaOrgHumanReadable(): array
    {
        $list = [];
        $schemaBuilders = $this->getSchemasOrg();
        foreach ($schemaBuilders as $schemaBuilderObject) {
            $v = $schemaBuilderObject->getInfoLink($this->getOwner());
            if ($v) {
                $list[] = $v;
            }
        }
        return $list;
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
        $data = json_encode($this->getSchemaOrgTestData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $array = json_decode($data, true);
        $array = $this->beautifySchemaDotOrgData($array);
        $fields->addFieldsToTab(
            'Root.Schema',
            [
                LiteralField::create(
                    'SchemaDotOrgTestLinkNice',
                    '<p><h2>Review actual data</h2><a href="' . $this->getSchemaTestLink() . '" target="_blank" rel="noopener noreferrer">Review Schema for ' . $this->getOwner()->Title . '</a></p>'
                ),
                LiteralField::create(
                    'SchemaDotOrgPrintOutTypes',
                    '<h2 style="margin-top: 20px">Schema Types</h2>' . ExpandableArrayList::create($this->getSchemaOrgHumanReadable())->setAllowHtmlAsIs(true)->forTemplate()
                ),
                ExpandableArrayListField::create(
                    'ExploreData',
                    'List of Actual Data',
                    $array
                ),
                LiteralField::create(
                    'SchemaDotOrgPrintOutDetails',
                    '<h2 style="margin-top: 20px">Raw Data</h2><pre>' . json_encode($this->getSchemaOrgTestData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>'
                ),
            ]
        );
    }

    public function getSchemaTestLink(): string
    {
        return 'https://validator.schema.org/#' . urlencode($this->getOwner()->AbsoluteLink());

    }

    protected function beautifySchemaDotOrgData(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->beautifySchemaDotOrgData($value);
            } elseif ($key === '@context') {
                unset($data[$key]);
            }
            // elseif ($key === '@type') {
            //     $value = "<b style=\"color: var(--pink, #e83e8c);\">$value</b>";
            // } else
        }
        unset($value);
        return $data;
    }

    protected function getSchemaOrgTestData(): array
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
