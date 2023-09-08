<?php

namespace Goldfinch\Service;

use MailchimpMarketing\ApiClient;
use SilverStripe\Core\Environment;
use MailchimpMarketing\ApiException;
use SilverStripe\Control\Controller;
use GuzzleHttp\Exception\ClientException;

class Mailchimp
{
    /**
     * https://mailchimp.com/developer/marketing/api/
     */

    private static $client;
    private static $api_key;
    private static $server;
    private static $list_id;
    private static $errors;

    public function __construct($api_key, $server, $list_id = null)
    {
        self::initAppEnv();

        if ($api_key)
        {
            self::$api_key = $api_key;
        }

        if ($server)
        {
            self::$server = $server;
        }

        if ($list_id)
        {
            self::$list_id = $list_id;
        }

        self::initClient();
    }

    public static function setApiKey($api_key)
    {
        self::$api_key = $api_key;
    }

    public static function setListId()
    {
        self::$list_id = $list_id;
    }

    public static function setServer()
    {
        self::$server = $server;
    }

    // --- API Methods START

    public static function addListMember($options, $list_id = null)
    {
        if (!self::$client)
        {
            self::initAppEnv();
            self::initClient();
        }

        if (!$list_id)
        {
            $list_id = self::$list_id;
        }

        return self::call(function() use ($list_id, $options) {
            return self::$client->lists->addListMember($list_id, $options);
        }, $options);
    }

    // --- API Methods END

    private static function initClient()
    {
        self::$client = new ApiClient();

        self::$client->setConfig([
            'apiKey' => self::$api_key,
            'server' => self::$server,
        ]);
    }

    private static function initAppEnv()
    {
        if (class_exists(Environment::class, false)) // SilverStripe
        {
            self::$api_key = ss_env('APP_SERVICE_MAILCHIMP_API_KEY');
            self::$server = ss_env('APP_SERVICE_MAILCHIMP_SERVER');
            self::$list_id = ss_env('APP_SERVICE_MAILCHIMP_LIST_ID');
        }
        else if (function_exists('env')) // Laravel
        {
            self::$api_key = env('APP_SERVICE_MAILCHIMP_API_KEY');
            self::$server = env('APP_SERVICE_MAILCHIMP_SERVER');
            self::$list_id = env('APP_SERVICE_MAILCHIMP_LIST_ID');
        }
    }

    private static function call($call, $options)
    {
        try {
            $response = $call();
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());
            self::setErrors($response, $options);
            self::abort(self::getErrors());
        } catch (ApiException $e) {
            $response = $e->getResponseBody();
            self::setErrors($response, $options);
            self::abort(self::getErrors());
        }

        return self::getErrors() ?? $response;
    }

    private static function setErrors($response, $options)
    {
        if ($response->title == 'Member Exists')
        {
            self::$errors = [$options['_field'] => 'Seems like ' . $options['email_address'] . ' is already subscribed.'];
        }
    }

    public static function getErrors()
    {
        return self::$errors;
    }

    protected static function abort($data, $code = 422)
    {
        return Controller::curr()->httpError($code, json_encode($data));
    }
}
