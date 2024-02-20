<?php
/**
 * Website.php
 *
 * @author Bram de Leeuw
 * Date: 04/11/16
 */

namespace Broarm\Schema\Builders;

use Broarm\Schema\SchemaBuilder;
use DateTimeImmutable;
use Spatie\SchemaOrg\WebPage;

/**
 * Class Website
 */
class WebPageSchema extends SchemaBuilder
{
    /**
     * Create the website schema object
     *
     * @param \Page $page
     *
     **/
    public function getSchema($page): WebPage
    {

        $webpage = new WebPage();
        $webpage->name($page->Title);
        $webpage->url($page->AbsoluteLink());
        $webpage->dateCreated(new DateTimeImmutable($page->Created));
        $webpage->dateModified(new DateTimeImmutable($page->LastEdited));
        $webpage->description($page->MetaDescription);

        return $webpage;
    }
}
