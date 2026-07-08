<?php

namespace Broarm\Schema\Model;

use SilverStripe\ORM\DataObject;

class SchemaDefinitionValues extends DataObject
{
    private static $table_name = 'SchemaDefinitionValues';

    private static $db = [
        'FieldName' => 'Varchar(255)',
        'Value' => 'Varchar(255)',
        'LinkedFieldName' => 'Varchar(255)',
    ];
}
