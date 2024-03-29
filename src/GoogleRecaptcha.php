<?php

namespace Goldfinch\Service;

use ReCaptcha\ReCaptcha;
use SilverStripe\Core\Environment;

class GoogleRecaptcha
{
    public $client;
    private $secret_key;

    public function __construct($secret_key = null)
    {
        $this->initAppEnv();

        if ($secret_key) {
            $this->secret_key = $secret_key;
        }

        $this->initClient();
    }

    private function initClient()
    {
        $this->client = new ReCaptcha($this->secret_key);
    }

    private function initAppEnv()
    {
        if (class_exists(Environment::class, false)) {
            // SilverStripe
            $this->secret_key = Environment::getEnv(
                'APP_GOOGLE_RECAPTCHA_SECRET_KEY',
            );
        } elseif (function_exists('env')) {
            // Laravel
            $this->secret_key = env('APP_GOOGLE_RECAPTCHA_SECRET_KEY');
        }
    }
}
