<?php

class AmoCrmClass {
    const TOKEN_FILE = "../core/amo/TOKEN.txt";
    const SUB_DOMAIN = "olegkovalev553";
    const CLIENT_ID = "c5df4c43-a1b6-4d9e-b868-4cdaea34de76";
    const CLIENT_SECRET = "nq0p2g5kYozPCGLtLHN603xSNQ6nowQziTIFaMoSddtBLyZ9wT7K96VYqESpIr1x";
    const CODE = "def50200f70a42ed94da7b0e07c5d47c91557613e400c857213e37fc998309462bf902d3e6f7b3c286ea9c6b741295bc83a65f2f3ec4d95978ae38d6d089425056f957ea8739bed40956fdc1c628f981a7d2e76ef48d356c8fbe56b41cc357c92b1163f83df32e895bc670da15155881d2dde694494cbecbccbfa8b04a07ee55a4a2c8579407d5c49869d0a769ffddc3cf3f19165449444f7dc95441b269950c75a1e49c463a841b05f8ceaf896c2a4f1f8e0933cb42526d1bf06d2cdebb59442bb958fffc9b7f5a5573e10044442153a08b96fddcf4a85b2bc30c587a824bca75d4e0ebca609120d487638a2bd1c75247dc1d23c6beae0b8b530bea39a6085acfeda01d917c646c4a284bcb9e260d3fb6ea6c6619bfd2e0c87981fb2beb7511b23f23f84e23707df96cbce8ed6714206e9bf66e8f1d81c6946e0dbf176c03fa806fc46a6938c860aa8678458af10e43efc3b291aba77aa5df0dbc350e84b5dfa6f8e341feaa7405d7cb125c242f1d8a21521213be506ece1942a8a8674d3af732ccef422c50df1609f4a971fa3ebec83992b0d697de62ffb9cd0d7b497786bce424425e7a7009ba1a37fed0123ee6fe561b65f8c810e1797e9c13f1e7452292f759c5b640b5d9fadd4290a13fbe9e48d4757316e61fe2b4714375bc40fc638e6f3c55bf7154740ca073e19802";
    const REDIRECT_URL = "https://app.jaycopilot.com/";

    function __construct() {
        if(file_exists(AmoCrmClass::TOKEN_FILE)) {
            $expires_in = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'expires_in'};
            if($expires_in < time()) {
                $this->access_token = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'access_token'};
                $this->getToken(true);
            }
            else
                $this->access_token = json_decode(file_get_contents(AmoCrmClass::TOKEN_FILE))->{'access_token'};
        }
        else
            $this->getToken();
    }

    private function getToken($refresh = false): void {
        $link = 'https://' . AmoCrmClass::SUB_DOMAIN . '.amocrm.ru/oauth2/access_token';

        if($refresh) {
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

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
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

        try
        {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        }
        catch(Exception $e)
        {
            echo $out;
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        $response = json_decode($out, true);

        $this->access_token = $response['access_token'];

        $token = [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'token_type' => $response['token_type'],
            'expires_in' => time() + $response['expires_in']
        ];

        file_put_contents(AmoCrmClass::TOKEN_FILE, json_encode($token));
    }

    private function tokenCurlRequest($link, $PostFields = []): string {
        $headers = [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ];

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
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        );

        try {
            if ($code != 200 && $code != 204) {
                throw new Exception($errors[$code] ?? 'Undescribed error', $code);
            }
        } catch (Exception $E) {
            $this->Error('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode() . $link);
        }


        return $output;
    }

    public function apiPostRequest($service, $params = []): array {
        $result = '';
        try {
            $url = 'https://' . AmoCrmClass::SUB_DOMAIN . '.amocrm.ru/api/v4/' . $service;
            $result = json_decode($this->tokenCurlRequest($url, $params), true);
            usleep(250000); //???
        } catch (ErrorException $e) {
            $this->Error($e);
        }

        return $result;
    }

    private function error($e): void {
        file_put_contents("ERROR_LOG.txt", $e);
    }
}