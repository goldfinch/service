<?php

namespace Goldfinch\Service;

use SendGrid\Mail\Mail;
use SendGrid as SendGridCore;
use SilverStripe\Core\Environment;
use SilverStripe\Control\Controller;

class SendGrid
{
    private static $client;

    private static $api_key;

    public function __construct($api_key = null)
    {
        self::initAppEnv();

        if ($api_key) {
            self::$api_key = $api_key;
        }

        self::initClient();
    }

    public static function setApiKey($api_key)
    {
        self::$api_key = $api_key;
    }

    // --- API Methods START

    // TODO: make error respons friendly for formik
    public static function send($data)
    {
        self::initAppEnv();
        self::initClient();

        $mail = new Mail();
        $mail->setFrom(trim($data['from']), $data['name']);

        if (is_array($data['to'])) {
            $mail->addTos($data['to']);
        } else {
            $mail->addTo($data['to']);
        }

        $mail->setSubject($data['subject']);
        $mail->setReplyTo(trim($data['reply_to']), isset($data['reply_name']) ? $data['reply_name'] : $data['name']);

        if (isset($data['bcc'])) {
            if (is_array($data['bcc']) && count($data['bcc'])) {
                $mail->addBccs($data['bcc']);
            } else {
                $mail->addBcc($data['bcc']);
            }
        }

        if (isset($data['cc'])) {
            if (is_array($data['cc']) && count($data['cc'])) {
                $mail->addCcs($data['cc']);
            } else {
                $mail->addCc($data['cc']);
            }
        }

        $mail->addContent('text/html', $data['body']);

        // attachments
        if (isset($data['attachments']) && count($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $file_encoded = base64_encode(file_get_contents($attachment['tmp_name']));
                $mail->addAttachment(
                    $file_encoded,
                    'application/octet-stream',
                    $attachment['name'],
                    'attachment'
                );
            }
        }

        $return = [];

        try {
            $response = self::$client->send($mail);

            // dd($response);

            $return = [
                'statusCode' => $response->statusCode(),
                'message' => '', // $response->body()
            ];
        } catch (Exception $e) {
            $return = [
                'statusCode' => $e->getMessage(),
                'message' => '', // $e->getBody(),
            ];
        }

        if ($return['statusCode'] != 202) {
            return self::abort(
                'Sorry, something went wrong. Please, try again.',
            );
            // if (Controller::has_curr()) {
            //     return Controller::curr()->httpError($return['statusCode'], json_encode($return));
            // }
        }

        return $return;
    }

    // --- API Methods END

    private static function initClient()
    {
        self::$client = new SendGridCore(self::$api_key);
    }

    private static function initAppEnv()
    {
        if (class_exists(Environment::class, false)) {
            // SilverStripe
            self::$api_key = Environment::getEnv(
                'APP_SERVICE_SENDGRID_API_KEY',
            );
        } elseif (function_exists('env')) {
            // Laravel
            self::$api_key = env('APP_SERVICE_SENDGRID_API_KEY');
        }
    }

    protected static function abort($data, $code = 422)
    {
        if (Controller::has_curr()) {
            return Controller::curr()->httpError($code, json_encode($data));
        } else {
            exit;
        }
    }
}
