<?php

namespace Broarm\Schema\Model;

use SilverStripe\ORM\DataObject;

class SchemaDefinition extends DataObject
{
    private static $table_name = 'SchemaDefinition';

    private static $db = [
        'TypeClass' => 'Varchar(255)',
        'TypeName' => 'Varchar(255)',
        'AlignsToClassName' => 'Varchar(255)',
    ];

    private static $has_many = [
        'Values' => SchemaDefinitionValues::class,
    ];
}
