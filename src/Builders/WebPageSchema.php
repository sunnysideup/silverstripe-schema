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
use SilverStripe\Core\Config\Config;
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
        $locale = Config::inst()->get(i18n::class, 'default_locale');
        if ($page->hasMethod('getLocale')) {
            $locale = $page->getLocale() ?: $locale;
        }
        $locale = str_replace('_', '-', $locale);
        $webpage = new WebPage();
        $webpage->name($this->escapeJson($page->Title));
        $webpage->url($page->AbsoluteLink());
        $webpage->id($page->AbsoluteLink());
        $webpage->dateCreated(new DateTimeImmutable((string) $page->Created));
        $webpage->dateModified(new DateTimeImmutable((string) $page->LastEdited));
        $webpage->description($this->escapeJson($page->MetaDescription));
        $webpage->inLanguage($locale);

        return $webpage;
    }
}
