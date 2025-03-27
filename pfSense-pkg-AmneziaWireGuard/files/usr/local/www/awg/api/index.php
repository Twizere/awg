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
$pconfig = config_get_path(AMNEZIAWG_BASE_PATH . '/api', []);

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
function respond($status, $data = '', $message = '')
{
    http_response_code($status);
    $response = [];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    if (!empty($message)) {
        $response['message'] = $message;
    }
    echo json_encode($response);
    exit;
}


function authenticate($apiKey)
{
    $providedKey = "";
    if (!empty($apiKey) && isset($apiKey)) {
        $providedKey = $_SERVER['HTTP_X_API_KEY'];
    } else {
        respond(401, '', "Unauthorized: Key is empty ");
    }

    $apiConfig = getAPIConfig();

    // Check if API is enabled
    if (empty($apiConfig['api_enable']) || !$apiConfig['api_enable']) {
        respond(403, '', "API is disabled");
    }

    // Check authentication method
    $authMethod = $apiConfig['auth_method'] ?? 'none';

    switch ($authMethod) {
        case 'apikey':
            $configuredKey = $apiConfig['api_key'] ?? '';
            if (trim($providedKey) !== trim($configuredKey)) {
                respond(401, '', "Unauthorized: Invalid API Key. ");
            }
            break;

        case 'none':
            // No authentication required
            break;

        default:
            respond(400, '', "Invalid authentication method");
    }
}

function parsePeer($peer)
{
    return [
        'id' => $peer['id'],
        'description' => htmlspecialchars($peer['descr']),
        'public_key' => htmlspecialchars($peer['publickey']),
        'private_key' => htmlspecialchars($peer['privatekey']),
        'tunnel' => htmlspecialchars($peer['tun']),
        'allowed_ips' => array_map(function ($ip) {
            return "{$ip['address']}/{$ip['mask']}";
        }, $peer['allowedips']['row'] ?? []),
        'endpoint' => htmlspecialchars(wg_format_endpoint(false, $peer)),
        'enabled' => ($peer['enabled'] == 'yes'),
    ];
}
function parseTunnel($tunnel)
{
    $peers = wg_tunnel_get_peers_config($tunnel['name']);
    return [
        'name' => htmlspecialchars($tunnel['name']),
        'description' => htmlspecialchars($tunnel['descr']),
        'public_key' => htmlspecialchars($tunnel['publickey']),
        'address' => array_map(function ($ip) {
            return "{$ip['address']}/{$ip['mask']}";
        }, $tunnel['addresses']['row'] ?? []),
        'listen_port' => htmlspecialchars($tunnel['listenport']),
        'peer_count' => count($peers),
        'enabled' => ($tunnel['enabled'] == 'yes'),
    ];
}


function listPeers()
{
    $peers = config_get_path(AMNEZIAWG_BASE_PATH . '/peers/item', []);

    if (count($peers) > 0) {
        $peerList = [];
        foreach ($peers as $peer) {
            $peerList[] = parsePeer($peer);
        }
        return $peerList;
    } else {
        respond(200, '', 'No peers have been configured.');
    }
}


function listTunnels()
{
    $tunnels = config_get_path(AMNEZIAWG_BASE_PATH . '/tunnels/item', []);

    if (count($tunnels) > 0) {
        $tunnelList = [];
        foreach ($tunnels as $tunnel) {
            $tunnelList[] = parseTunnel($tunnel);
        }
        return $tunnelList;
    } else {
        respond(200, '', 'No tunnels have been configured.');
    }
}


function addPeer($peerData)
{
    global $config;

    $peersPath = AMNEZIAWG_BASE_PATH . '/peers/item';
    $peers = config_get_path($peersPath, []);

    // Generate a UUID for the new peer
    $peerData['id'] = wg_generate_uuid();

    // Validate required fields
    $requiredFields = ['tun', 'publickey', 'privatekey'];
    foreach ($requiredFields as $field) {
        if (empty($peerData[$field])) {
            respond(400, '', "Missing required field: $field");
        }
    }

    // Prepare peer configuration
    $peerConfig = [
        'id' => $peerData['id'],
        'enabled' => $peerData['enabled'] ?? 'no',
        'tun' => $peerData['tun'],
        'descr' => $peerData['descr'] ?? '',
        'endpoint' => $peerData['endpoint'] ?? '',
        'port' => $peerData['port'] ?? '',
        'persistentkeepalive' => $peerData['persistentkeepalive'] ?? '',
        'privatekey' => $peerData['privatekey'],
        'publickey' => $peerData['publickey'],
        'presharedkey' => $peerData['presharedkey'] ?? '',
        'allowedips' => [
            'row' => $peerData['allowedips'] ?? [],
        ],
    ];

    // Add the new peer to the configuration
    $peers[] = $peerConfig;
    config_set_path($peersPath, $peers);

    // Save the configuration
    write_config("Added new peer with ID {$peerData['id']}");

    // Resync the package
    wg_resync();

    respond(200, $peerConfig, "Peer added successfully");
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

function applyFirewallRules($interface, $ipCidr)
{
    // Enable IP forwarding
    exec("sysctl net.inet.ip.forwarding=1", $output, $status);
    if ($status !== 0) {
        respond(404, '', "Failed to enable IP forwarding");
    }

    // Make IP forwarding permanent
    file_put_contents('/etc/rc.conf', "gateway_enable=\"YES\"\n", FILE_APPEND);

    // Enable PF and PF logging in /etc/rc.conf
    $rcConfUpdates = [
        "pf_enable=\"YES\"",
        "pflog_enable=\"YES\"",
    ];
    foreach ($rcConfUpdates as $line) {
        file_put_contents('/etc/rc.conf', $line . "\n", FILE_APPEND);
    }

    // Create or update the PF configuration file
    $pfConf = <<<EOT
        set skip on lo0
        nat on vtnet0 from {$interface}:network to any -> (vtnet0)
        pass in on {$interface} from any to {$ipCidr} keep state
        pass out on vtnet0 from {$ipCidr} to any keep state
        EOT;

    file_put_contents('/etc/pf.conf', $pfConf);

    // Reload PF configuration
    exec("service pf start", $output, $status);
    if ($status !== 0) {
        respond(404, '', "Failed to start PF service");
    }


    

    // Apply the PF rules
    exec("pfctl -f /etc/pf.conf", $output, $status);
    if ($status !== 0) {
        respond(404, '', "Failed to apply PF rules");
    }

    return $status; // Return 0 for success
}

function reloadAWG($interface)
{
    $output = [];
    $status = 0;

    // Restart the WireGuard service
    // exec("service amneziawg restart 2>&1", $output, $status);
    // if ($status !== 0) {
    //     return "Failed to restart WireGuard service. Output: " . implode("\n", $output);
    // }

    // // Check the output for success confirmation
    // if (strpos(implode("\n", $output), "AmneziaWireGuard service is already running") !== false) {
    //     return "WireGuard service is already running.";
    // }

    // return "WireGuard service restarted successfully.";

    // Apply firewall rules
    $ipCidr = "50.0.0.1/24";
    $firewallStatus = applyFirewallRules($interface, $ipCidr);

    return $firewallStatus === 0 ? "WireGuard service restarted and firewall rules applied successfully." : "Failed to apply firewall rules.";
}

function getInputData()
{
    $input = $_POST;
    if (empty($input)) {
        respond(400, '', "No POST data received");
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
        case "tunnels":
            respond(200, listTunnels());
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


respond(404, '',"Invalid request");
?>