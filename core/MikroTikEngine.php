<?php
// MikroTik API Logic | منطق ارتباط با میکروتیک
require_once __DIR__ . '/../libs/routeros/routeros_api.class.php';

class MikroTikEngine {
    private $api;

    public function __construct() { $this->api = new RouterosAPI(); }

    public function connectRouter($ip, $user, $pass) {
        return $this->api->connect($ip, $user, $pass);
    }

    public function createService($data) {
        // 1. Add WireGuard Peer | افزودن پیر وایرگارد
        $this->api->comm("/interface/wireguard/peers/add", [
            "interface" => "wg0",
            "public-key" => $data['public_key'],
            "allowed-address" => $data['remote_ip'] . "/32",
            "comment" => $data['username']
        ]);

        // 2. Add Simple Queue for Speed & Volume | افزودن صف محدودیت
        $this->api->comm("/queue/simple/add", [
            "name" => "vms_" . $data['username'],
            "target" => $data['remote_ip'],
            "max-limit" => $data['up_speed'] . "/" . $data['down_speed'],
            "total-max-limit" => $data['total_volume_bytes']
        ]);
    }

    public function removeService($username, $remote_ip) {
        $this->api->comm("/interface/wireguard/peers/remove", [
            ".id" => $this->api->comm("/interface/wireguard/peers/print", ["?comment" => $username])[0]['.id']
        ]);
        $this->api->comm("/queue/simple/remove", [
            ".id" => $this->api->comm("/queue/simple/print", ["?target" => $remote_ip . "/32"])[0]['.id']
        ]);
    }
}