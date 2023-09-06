<?php

namespace Goldfinch\Service\Providers;

use Carbon\Carbon;
use SilverStripe\View\TemplateGlobalProvider;

class HelpersTemplateProvider implements TemplateGlobalProvider
{
    /**
     * @return array|void
     */
    public static function get_template_global_variables(): array
    {
        return [
            'Carbon',
        ];
    }

    public static function Carbon($format, $date = null)
    {
        if ($date)
        {
            return Carbon::createFromFormat($format, $date);
        }
        else
        {
            return Carbon::now()->format($format);
        }
    }
}
