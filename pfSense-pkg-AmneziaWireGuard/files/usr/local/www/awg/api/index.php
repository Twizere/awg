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

// Grab current configuration from the XML
$pconfig = config_get_path('installedpackages/amneziawg/api', []);

ignore_user_abort(true);

$apiConfig = getAPIConfig();
if (empty($apiConfig)) {
    $defaultConfig = [
        "api_enable" => true,
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


function authenticate($apiKey) {
    $providedKey="";
    if (!empty($apiKey) && isset($apiKey)) {
        $providedKey = $_SERVER['HTTP_X_API_KEY'];
    }else {
        respond(401, "Unauthorized: Key is empty ");
    }

    $apiConfig = getAPIConfig();

    // Check if API is enabled
    if (empty($apiConfig['api_enable']) || !$apiConfig['api_enable']) {
        respond(403, "API is disabled");
    }

    // Check authentication method
    $authMethod = $apiConfig['auth_method'] ?? 'none';

    switch ($authMethod) {
        case 'apikey':
            $configuredKey = $apiConfig['api_key'] ?? '';
            if (trim($providedKey) !== trim($configuredKey)) {
                respond(401, "Unauthorized: Invalid API Key. ");
            }
            break;

        case 'none':
            // No authentication required
            break;

        default:
            respond(400, "Invalid authentication method");
    }
}

function listPeers() {
   
}
 
// function applyFirewallRules($interface, $ipCidr) {
//     $rules = [
//         "set skip on lo0",
//         "nat on vtnet0 from {$ipCidr} to any -> (vtnet0)",
//         "pass in on $interface from any to $ipCidr keep state",
//         "pass out on vtnet0 from $ipCidr to any keep state",
//     ];
//     file_put_contents('/etc/pf.conf', implode("\n", $rules) . "\n");
//     exec("pfctl -f /etc/pf.conf", $output, $status);
//     return $status === 0 ? "Firewall rules applied successfully." : "Failed to apply firewall rules.";
// }
function getInputData() {
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        respond(400, "Invalid Content-Type. Expected application/json");
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(400, "Invalid JSON input");
    }
    return $input;
}


$request = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$apiKey = $_SERVER['HTTP_X_API_KEY'];
$iface = $_SERVER['X-INTERFACE-NAME'];
 
authenticate($apiKey);
$input = getInputData();

if ($request == "POST") {
    

    $action = $input['act'] ?? '';
    switch ($action) {
        case "peers":
            respond(200, listPeers());
            break;

        case "reload":
            $interface = $input['interface'] ?? '';
            if (!$interface) respond(400, "Missing interface name");
            respond(200, reloadAWG($interface));
            break;

        default:
            respond(400, "Invalid action specified");
    }
}


respond(404, "Invalid request");
?>
