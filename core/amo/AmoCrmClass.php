<?php

class AmoCrmClass {
    const TOKEN_FILE = '../core/amo/TOKEN.txt';

    const SUB_DOMAIN = '';
    const CLIENT_ID = '';
    const CLIENT_SECRET = '';
    const CODE = '';
    const REDIRECT_URL = '';

    function __construct() {
        if(file_exists(AmoCrmClass::TOKEN_FILE)) {
            $expires_in = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'expires_in'};
            if ($expires_in < time()) {
                $this->access_token = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'access_token'};
                $this->getToken(true);
            } else {
                $this->access_token = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'access_token'};
            }
        } else {
            $this->getToken();
        }
    }

    private function getToken($refresh = false): void {
        $link = 'https://' . AmoCrmClass::SUB_DOMAIN . '.amocrm.ru/oauth2/access_token';

        if ($refresh) {
            $data = [
                'client_id' => AmoCrmClass::CLIENT_ID,
                'client_secret' => AmoCrmClass::CLIENT_SECRET,
                'grant_type' => 'refresh_token',
                'refresh_token' => json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'refresh_token'},
                'redirect_uri' => AmoCrmClass::REDIRECT_URL
            ];
        } else {
            $data = [
                'client_id' => AmoCrmClass::CLIENT_ID,
                'client_secret' => AmoCrmClass::CLIENT_SECRET,
                'grant_type' => 'authorization_code',
                'code' => AmoCrmClass::CODE,
                'redirect_uri' => AmoCrmClass::REDIRECT_URL
            ];
        }

        $headers = ['Content-Type:application/json'];
        $response = json_decode($this->curlRequest($link, $headers, $data), true);

        $this->access_token = $response['access_token'];

        $token = [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'token_type' => $response['token_type'],
            'expires_in' => time() + $response['expires_in']
        ];

        file_put_contents(AmoCrmClass::TOKEN_FILE, json_encode($token));
    }

    private function curlRequest($link, $headers, $PostFields = []): string {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($PostFields));
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $output = curl_exec($curl);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        try {
            if ($code < 200 || $code > 204) {
                throw new Exception($errors[$code] ?? 'Undefined error', $code);
            }
        } catch(Exception $e) {
            echo $output;
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        return $output;
    }

    public function apiPostRequest($service, $params = []): array {
        $headers = [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ];
        $result = '';

        try {
            $url = 'https://' . AmoCrmClass::SUB_DOMAIN . '.amocrm.ru/api/v4/' . $service;
            $result = json_decode($this->curlRequest($url, $headers, $params), true);
        } catch (Exception $e) {
            $this->Error($e);
        }

        return $result;
    }

    private function error($e): void {
        file_put_contents('ERROR_LOG.txt', $e);
    }
}