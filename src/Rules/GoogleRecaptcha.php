<?php

namespace Goldfinch\Service\Rules;

use SilverStripe\Core\Environment;
use SilverStripe\Control\Director;
use Goldfinch\Service\GoogleRecaptcha as GoogleRecaptchaService;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class GoogleRecaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('The :attribute must be uppercase.');
        }

        $recaptcha = new GoogleRecaptchaService(Environment::getEnv('APP_GOOGLE_RECAPTCHA_SECRET_KEY'));

        $response = $recaptcha->client
            // ->setExpectedHostname($hostname)
            ->setExpectedHostname($_SERVER['HTTP_HOST'])
            ->verify($value, $_SERVER['REMOTE_ADDR']);

        if(!$response->isSuccess()) {

            // $response->getErrorCodes()
            $fail('The :attribute invalid.');
        }
    }
}
