<?php

// pfSense includes
require_once('functions.inc');
require_once('config.inc');
// require_once('globals.inc');
// require_once('gwlb.inc');
// require_once('util.inc');
// require_once('services.inc');
// require_once('service-utils.inc');

// Amnezia WireGuard includes
require_once('amneziawireguard/includes/wg.inc');
require_once('amneziawireguard/includes/wg_guiconfig.inc');

header("Content-Type: application/json");
define('AMNEZIAWG_BASE_PATH', 'installedpackages/amneziawg');
// Grab current configuration from the XML
$pconfig = config_get_path('installedpackages/amneziawg/api', []);

//ignore_user_abort(true);

$apiConfig = getAPIConfig();
if (empty($apiConfig)) {
    $defaultConfig = [
        "api_enable" => true,
    ];
    saveAPIConfig($defaultConfig);
}
function saveAPIConfig($configData)
{
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

function getAPIConfig()
{
    global $config;

    return $config['installedpackages']['amneziawg']['api'] ?? [];
}
function respond($status, $message)
{
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit;
}


function authenticate($apiKey)
{
    $providedKey = "";
    if (!empty($apiKey) && isset($apiKey)) {
        $providedKey = $_SERVER['HTTP_X_API_KEY'];
    } else {
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

function listPeers()
{
    $peers = config_get_path(AMNEZIAWG_BASE_PATH . '/peers/item', []);

    if (count($peers) > 0) {
        $peerList = [];
        foreach ($peers as $peer_idx => $peer) {
            $peerList[] = [
                'description' => htmlspecialchars(wg_truncate_pretty($peer['descr'], 16)),
                'public_key' => htmlspecialchars(wg_truncate_pretty($peer['publickey'], 16)),
                'tunnel' => htmlspecialchars($peer['tun']),
                'allowed_ips' => wg_generate_peer_allowedips_popup_link($peer_idx),
                'endpoint' => htmlspecialchars(wg_format_endpoint(false, $peer)),
                'enabled' => ($peer['enabled'] == 'yes'),
            ];
        }
        return $peerList;
    } else {
        return ['message' => 'No  peers have been configured.'];
    }
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
function getInputData()
{
    $input = $_POST;
    if (empty($input)) {
        respond(400, "No POST data received");
    }
    return $input;
}

function getJsonInputData()
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(400, "Invalid JSON input: " . json_last_error_msg());
    }

    if (empty($data)) {
        respond(400, "No JSON data received");
    }

    return $data;
}

function getHttpVariables()
{
    $httpVariables = [
        'GET' => $_GET,
        'POST' => $_POST,
        'PUT' => getJsonInputData(),
        'SERVER' => $_SERVER,
        'FILES' => $_FILES,
        'COOKIE' => $_COOKIE,
        'REQUEST' => $_REQUEST,
        'SESSION' => isset($_SESSION) ? $_SESSION : null,
        'ENV' => $_ENV,
    ];
    respond(200, $httpVariables);

}


$uri = $_SERVER['REQUEST_URI'];
$apiKey = $_SERVER['HTTP_X_API_KEY'];
$iface = $_SERVER['X-INTERFACE-NAME'];

//getHttpVariables();
authenticate($apiKey);
$input = getJsonInputData();

if ($input) {


    $action = $input['act'] ?? '';
    switch ($action) {
        case "peers":
            respond(200, listPeers());
            break;

        case "reload":
            $interface = $input['interface'] ?? '';
            if (!$interface)
                respond(400, "Missing interface name");
            respond(200, reloadAWG($interface));
            break;

        default:
            respond(400, "Invalid action specified");
    }
}


respond(404, "Invalid request");
?>