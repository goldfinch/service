<?php

namespace Goldfich\Service;

use SilverStripe\Core\Environment;
use MailchimpMarketing\ApiClient;

class Mailchimp
{
    private $client;

    private $api_key;
    private $server;
    private $list_id;

    public function __construct($api_key, $server, $list_id = null)
    {
        $this->initAppEnv();

        $this->api_key = $api_key;
        $this->server = $server;
        $this->list_id = $list_id;

        $this->initClient();
    }

    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    public function setListId()
    {
        $this->list_id = $list_id;
    }

    public function setServer()
    {
        $this->server = $server;
    }

    // --- API Methods START

    public function addListMember($list_id, $options)
    {
        return $this->call(function() use ($list_id, $options) {
            return $mailchimp->lists->addListMember($list_id, $options);
        });
    }

    // --- API Methods END

    private function initClient()
    {
        $this->client = new ApiClient();

        $this->client->setConfig([
            'apiKey' => $this->api_key,
            'server' => $this->server,
        ]);
    }

    private function initAppEnv()
    {
        if (class_exists(Environment::class, false)) // SilverStripe
        {
            $this->api_key = Environment::getEnv('APP_SERVICE_MAILCHIMP_API_KEY');
            $this->server = Environment::getEnv('APP_SERVICE_MAILCHIMP_SERVER');
            $this->list_id = Environment::getEnv('APP_SERVICE_MAILCHIMP_LIST_ID');
        }
        else if (function_exists('env')) // Laravel
        {
            $this->api_key = env('APP_SERVICE_MAILCHIMP_API_KEY');
            $this->server = env('APP_SERVICE_MAILCHIMP_SERVER');
            $this->list_id = env('APP_SERVICE_MAILCHIMP_LIST_ID');
        }
    }

    private function call($call)
    {
        try {
            $response = $call();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());
        } catch (MailchimpMarketing\ApiException $e) {
            $response = $e->getResponseBody();
        }

        return $response;
    }
}
