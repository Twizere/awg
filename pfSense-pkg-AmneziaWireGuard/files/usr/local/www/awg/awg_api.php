<?php


// pfSense includes
require_once('functions.inc');
require_once('guiconfig.inc');

// WireGuard includes
require_once('amneziawireguard/includes/wg.inc');
require_once('amneziawireguard/includes/wg_guiconfig.inc');

global $wgg;

// Initialize $wgg state
wg_globals();

$save_success = false;

if ($_POST) {
	if (isset($_POST['apply'])) {
		$ret_code = 0;

		if (is_subsystem_dirty($wgg['subsystems']['wg'])) {
			
			if ($ret_code == 0) {
				clear_subsystem_dirty($wgg['subsystems']['wg']);
			}
		}
	}

	if (isset($_POST['act'])) {
		switch ($_POST['act']) {
			case 'save':
				$res = wg_do_api_settings_post($_POST);
				$input_errors = $res['input_errors'];
				$pconfig = $res['pconfig'];

				if (empty($input_errors) && $res['changes']) {
					// wg_toggle_wireguard();
					mark_subsystem_dirty($wgg['subsystems']['wg']);
					$save_success = true;
				}

				break;
            case 'genapikey':
                // Process ajax call requesting new key pair
                print(wg_gen_apikey(true));
                exit;
                break;
			default:
				// Shouldn't be here, so bail out.
				header('Location: /awg/awg_settings.php');
				break;
		}
	}
}

// A dirty string hack
$s = fn($x) => $x;

// Just to make sure defaults are properly assigned if anything is missing
wg_defaults_install();

// Grab current configuration from the XML
$pconfig = config_get_path('installedpackages/amneziawg/api', []);

// Map the XML whitelist data to the expected structure
if (!empty($pconfig['ip_whitelist']) && is_array($pconfig['ip_whitelist']['row'])) {
    foreach ($pconfig['ip_whitelist']['row'] as $index => $row) {
        $pconfig['ip_whitelist']['row'][$index] = [
            'address' => $row['whitelist_address'] ?? '',
            'mask' => $row['whitelist_address_subnet'] ?? '32',
            'descr' => $row['whitelist_address_descr'] ?? '',
        ];
    }
}




$active_tab = "Api";
include('amneziawireguard/includes/awg_header.inc');
$pgtitle[]= [$active_tab];
include("head.inc");
$pglinks = array('', '/awg/awg_tunnels.php', '@self');

wg_print_service_warning();

if ($save_success) {
	print_info_box(gettext('The changes have been applied successfully.'), 'success');
}

if (isset($_POST['apply'])) {
	print_apply_result_box($ret_code);
}

wg_print_config_apply_box();

if (!empty($input_errors)) {
	print_input_errors($input_errors);
}

display_top_tabs($tab_array);

$form = new Form(false);

$section = new Form_Section(gettext('API Settings'));

// Enable API
$api_enable = new Form_Checkbox(
    'api_enable',
    gettext('Enable API'),
    gettext('Enable API functionality'),
    $pconfig['api_enable'] == 'yes'
);

$section->addInput($api_enable);

// API Authentication Method
$auth_methods = array(
    'none' => gettext('None'),
    'apikey' => gettext('API Key'),
    // 'token' => gettext('Bearer Token'),
    // 'basic' => gettext('Basic Authentication')
);

$section->addInput(new Form_Select(
    'auth_method',
    gettext('Authentication Method'),
    $pconfig['auth_method'],
    $auth_methods
))->setHelp(gettext('Select the authentication method for API access.'));

// API Key
$group = new Form_Group(gettext('API Key'));

$group->add(new Form_Input(
    'api_key',
    gettext('API Key'),
    wg_secret_input_type(),
    $pconfig['api_key']
))->addClass('trim')
->setHelp(gettext('Provide the API key for authentication.') . 
    '<a id="copyapikey" style="cursor: pointer;" data-success-text="Copied" data-timeout="3000">' . gettext('Copy') . '</a>')
->setReadonly()
->setWidth(6);

$group->add(new Form_Button(
    'genapikey',
    gettext('Generate'),
    null,
    'fa-solid fa-key'
))->addClass('btn-primary btn-sm')
->setHelp(gettext('Generate a new API key ') );


$section->add($group);
$form->add($section);
// // Token Expiry
// $section->addInput(new Form_Input(
//     'token_expiry',
//     gettext('Token Expiry (in seconds)'),
//     'text',
//     $pconfig['token_expiry']
// ))->setHelp(gettext('Specify the token expiry time in seconds. Default is 3600 seconds (1 hour).'));

$section = new Form_Section(gettext('API Access Control'));

$section->addInput(new Form_StaticText(
    gettext('Hint'),
    gettext('Allowed IP entries here will be transformed into proper subnet start boundaries prior to validating and saving. ' .
            'These entries must be unique. Otherwise, traffic to the conflicting networks will only be routed to the last entry in the list.')
));

// Initialize the IP whitelist array if necessary
if (!is_array($pconfig['ip_whitelist'])
    || !is_array($pconfig['ip_whitelist']['row'])
    || empty($pconfig['ip_whitelist']['row'])) {
        array_init_path($pconfig, 'ip_whitelist/row/0');
        $pconfig['ip_whitelist']['row'][0]['mask'] = '32'; // Default to /32 mask for IPv4
}

$last = count($pconfig['ip_whitelist']['row']) - 1;

foreach ($pconfig['ip_whitelist']['row'] as $counter => $item) {
    $group = new Form_Group($counter == 0 ? gettext('Whitelisted IPs') : null);

    $group->addClass('repeatable');

    $group->add(new Form_IpAddress(
        "whitelist_address{$counter}",
        gettext('Allowed Subnet or Host'),
        $item['address'],
        'BOTH'
    ))->addClass('trim')
      ->setHelp($counter == $last ? gettext('IPv4 or IPv6 subnet or host allowed to access the API.') : '')
      ->addMask("whitelist_address_subnet{$counter}", $item['mask'], 128, 0)
      ->setWidth(4);

    $group->add(new Form_Input(
        "whitelist_address_descr{$counter}",
        gettext('Description'),
        'text',
        $item['descr']
    ))->setHelp($counter == $last ? gettext('Description for administrative reference (not parsed).') : '')
      ->setWidth(4);

    $group->add(new Form_Button(
        "deleterow{$counter}",
        gettext('Delete'),
        null,
        'fa-solid fa-trash-can'
    ))->addClass('btn-warning btn-sm');

    $section->add($group);
}

$section->addInput(new Form_Button(
    'addrow',
    gettext('Add Whitelisted IP'),
    null,
    'fa-solid fa-plus'
))->addClass('btn-success btn-sm addbtn');

// Rate Limiting
$section->addInput(new Form_Input(
    'rate_limit',
    gettext('Rate Limit (requests per minute)'),
    'text',
    $pconfig['rate_limit']
))->setHelp(gettext('Specify the maximum number of API requests allowed per minute. Set to 0 for no limit.'));

$form->add($section);

$form->addGlobal(new Form_Input(
    'act',
    '',
    'hidden',
    'save'
));

print($form);

?>

<nav class="action-buttons">
	<button type="submit" id="saveform" name="saveform" class="btn btn-sm btn-primary" value="save" title="<?=gettext('Save Settings')?>">
		<i class="fa fa-solid fa-save icon-embed-btn"></i>
		<?=gettext('Save')?>
	</button>
</nav>

<script type="text/javascript">
//<![CDATA[
events.push(function() {
	wgRegTrimHandler();

    $('#copyapikey').click(function () {
        var $this = $(this);
        var originalText = $this.text();

        // Copy the API key to the clipboard
        navigator.clipboard.writeText($('#api_key').val());

        $this.text($this.attr('data-success-text'));

        setTimeout(function() {
            $this.text(originalText);
        }, $this.attr('data-timeout'));

        // Prevents the browser from scrolling
        return false;
    });

    $("#genapikey").prop('type', 'button');

    // Generate a new API key
    $('#genapikey').click(function(event) {
        if ($('#api_key').val().length == 0 || confirm(<?=json_encode(gettext('Are you sure you want to generate a new API key? This will overwrite the existing key.'))?>)) {
            ajaxRequest = $.ajax({
                url: '/awg/awg_api.php',
                type: 'post',
                data: {act: 'genapikey'},
                success: function(response, textStatus, jqXHR) {
                    resp = JSON.parse(response);
                    $('#api_key').val(resp.api_key);
                }
            });
        }
    });


	// Save the form
	$('#saveform').click(function () {
		$(form).submit();
	});

	$('#resolve_interval_track').click(function () {
		updateResolveInterval(this.checked);
	});

	function updateResolveInterval(state) {
		$('#resolve_interval').prop( "disabled", state);
	}

	updateResolveInterval($('#resolve_interval_track').prop('checked'));
});
//]]>



</script>

<?php
// include('amneziawireguard/includes/wg_foot.inc');
include('foot.inc');
?>
