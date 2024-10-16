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
use SilverStripe\Core\Convert;
use SilverStripe\i18n\i18n;
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
        $webpage->name($this->escapeJson($page->Title));
        $webpage->url($page->AbsoluteLink());
        $webpage->id($page->AbsoluteLink());
        $webpage->dateCreated(new DateTimeImmutable((string) $page->Created));
        $webpage->dateModified(new DateTimeImmutable((string) $page->LastEdited));
        $webpage->description($this->escapeJson($page->MetaDescription));
        $webpage->inLanguage(i18n::get_locale());

        return $webpage;
    }
}
