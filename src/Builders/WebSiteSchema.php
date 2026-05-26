<?php

namespace Broarm\Schema\Builders;

use Broarm\Schema\SchemaBuilder;
use SilverStripe\Control\Director;
use SilverStripe\SiteConfig\SiteConfig;
use Spatie\SchemaOrg\WebSite as SchemaOrgWebSite;

/**
 * Class WebSiteSchema
 */
class WebSiteSchema extends SchemaBuilder
{
    /**
     * Create the website schema object
     *
     * @param \Page $page
     *
     **/
    public function getSchema($page): ?SchemaOrgWebSite
    {
        if ($page->URLSegment === 'home') {
            $siteConfig = SiteConfig::current_site_config();
            $website = new SchemaOrgWebSite();
            $website->name($this->escapeJson($siteConfig->Title));
            $website->url(Director::absoluteBaseURL());

            return $website;
        }
        return null;
    }
}
