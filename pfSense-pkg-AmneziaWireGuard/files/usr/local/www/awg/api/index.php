<?php

// pfSense includes
require_once('config.inc');
require_once('globals.inc');
require_once('gwlb.inc');
require_once('util.inc');
require_once('services.inc');
require_once('service-utils.inc');

// WireGuard includes
require_once('amneziawireguard/includes/wg.inc');

header("Content-Type: application/json");

define('AUTH_KEY_FILE', '/etc/awg/auth_key.enc');
define('CONFIG_DIR', '/etc/awg/configs/');
define('API_SECRET', 'your-secret-passphrase');
ignore_user_abort(true);

$apiConfig = getAPIConfig();
if (empty($apiConfig)) {
    $defaultConfig = [
        "enabled" => true,
        "version" => "1.0",
        "created_at" => date("Y-m-d H:i:s"),
    ];
    saveAPIConfig($defaultConfig);
}
function saveAPIConfig($configData) {
    global $config;

    if (!is_array($config['installedpackages'])) {
        $config['installedpackages'] = [];
    }

    if (!is_array($config['installedpackages']['amneziawg'])) {
        $config['installedpackages']['amneziawg'] = [];
    }

    if (!is_array($config['installedpackages']['amneziawg']['api'])) {
        $config['installedpackages']['amneziawg']['api'] = [];
    }

    $config['installedpackages']['amneziawg']['api'] = $configData;

    write_config("Updated AmneziaWireGuard API configuration");
}

function getAPIConfig() {
    global $config;

    return $config['installedpackages']['amneziawg']['api'] ?? [];
}
function respond($status, $message) {
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit;
}

function generateAPIKey() {
    return bin2hex(random_bytes(32));
}

function encryptAPIKey($apiKey) {
    return openssl_encrypt($apiKey, 'AES-256-CBC', API_SECRET, 0, substr(API_SECRET, 0, 16));
}

function decryptAPIKey() {
    $encrypted = file_get_contents(AUTH_KEY_FILE);
    return openssl_decrypt($encrypted, 'AES-256-CBC', API_SECRET, 0, substr(API_SECRET, 0, 16));
}

function authenticate() {
    $headers = getallheaders();
    if (!isset($headers["X-API-Key"]) || trim($headers["X-API-Key"]) !== decryptAPIKey()) {
        respond(401, "Unauthorized");
    }
}

function listPeers() {
    $configFiles = glob(CONFIG_DIR . "*.conf");
    $peers = [];
    foreach ($configFiles as $file) {
        $peers[] = basename($file);
    }
    return $peers;
}

function reloadAWG($interface) {
    exec("awg-quick down $interface && awg-quick up $interface", $output, $status);
    return $status === 0 ? "WireGuard restarted successfully." : "Failed to reload WireGuard.";
}

function applyFirewallRules($interface, $ipCidr) {
    $rules = [
        "set skip on lo0",
        "nat on vtnet0 from {$ipCidr} to any -> (vtnet0)",
        "pass in on $interface from any to $ipCidr keep state",
        "pass out on vtnet0 from $ipCidr to any keep state",
    ];
    file_put_contents('/etc/pf.conf', implode("\n", $rules) . "\n");
    exec("pfctl -f /etc/pf.conf", $output, $status);
    return $status === 0 ? "Firewall rules applied successfully." : "Failed to apply firewall rules.";
}

$request = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if ($request == "POST" && $uri == "/genkey") {
    $apiKey = generateAPIKey();
    file_put_contents(AUTH_KEY_FILE, encryptAPIKey($apiKey));
    respond(200, "New API key generated: " . $apiKey);
}

authenticate();

if ($request == "GET" && $uri == "/peers") {
    respond(200, listPeers());
}

if ($request == "POST" && $uri == "/reload") {
    $interface = $_POST['interface'] ?? '';
    if (!$interface) respond(400, "Missing interface name");
    respond(200, reloadAWG($interface));
}

if ($request == "POST" && $uri == "/applyFirewall") {
    $interface = $_POST['interface'] ?? '';
    $ipCidr = $_POST['ipCidr'] ?? '';
    if (!$interface || !$ipCidr) respond(400, "Missing parameters");
    respond(200, applyFirewallRules($interface, $ipCidr));
}

respond(404, "Invalid request");
?>
