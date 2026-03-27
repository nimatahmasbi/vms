<?php
// IPPanel SMS Integration | اتصال به پنل پیامک آی‌پی پنل
class SMSHandler {
    private $apiKey;
    private $patternCode;

    public function __construct() {
        $settings = include __DIR__ . '/../config/app_settings.php';
        $this->apiKey = $settings['ippanel_api_key'];
        $this->patternCode = $settings['ippanel_pattern_code'];
    }

    public function sendOTP($mobile, $code) {
        $url = "https://api2.ippanel.com/themes/compiled/otp/send";
        $data = [
            "code" => $code,
            "mobile" => $mobile,
            "pattern_code" => $this->patternCode
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: AccessKey ' . $this->apiKey));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}