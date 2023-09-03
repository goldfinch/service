<?php

namespace Goldfinch\Service;

use SilverStripe\Core\Environment;
use SendGrid as SendGridCore;
use SendGrid\Mail\Mail;

class SendGrid
{
    private $client;

    private $api_key;

    public function __construct($api_key)
    {
        $this->initAppEnv();

        $this->api_key = $api_key;

        $this->initClient();
    }

    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    // --- API Methods START

    public function send($data)
    {
        $mail = new Mail();
        $mail->setFrom($data['from']);
        $mail->addTo($data['to']);
        $mail->setSubject($data['subject']);
        $mail->setReplyTo($data['reply_to'], $data['reply_to_name']);
        $mail->addBcc($data['bcc']);
        $mail->addContent(
            'text/html', $data['body'],
        );

        return $this->client->send($mail);
    }

    // --- API Methods END

    private function initClient()
    {
        $this->client = new SendGridCore($this->api_key);
    }

    private function initAppEnv()
    {
        if (class_exists(Environment::class, false)) // SilverStripe
        {
            $this->api_key = Environment::getEnv('APP_SERVICE_SENDGRID_API_KEY');
        }
        else if (function_exists('env')) // Laravel
        {
            $this->api_key = env('APP_SERVICE_SENDGRID_API_KEY');
        }
    }
}
