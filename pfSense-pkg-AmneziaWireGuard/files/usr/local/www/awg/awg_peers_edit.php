<?php
/*
 * awg_peers_edit.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2021-2024 Rubicon Communications, LLC (Netgate)
 * Copyright (c) 2021 R. Christian McDonald (https://github.com/rcmcdonald91)
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

##|+PRIV
##|*IDENT=page-vpn-wireguard
##|*NAME=VPN: WireGuard: Edit
##|*DESCR=Allow access to the 'VPN: WireGuard' page.
##|*MATCH=awg_peers_edit.php*
##|-PRIV

// pfSense includes
require_once('functions.inc');
require_once('guiconfig.inc');

// WireGuard includes
require_once('amneziawireguard/includes/wg.inc');
require_once('amneziawireguard/includes/wg_guiconfig.inc');

global $wgg;

// Initialize $wgg state
wg_globals();

$pconfig = [];
$is_new = true;

if (isset($_REQUEST['tun'])) {
	$tun_name = $_REQUEST['tun'];
}

if (isset($_REQUEST['peer']) && is_numericint($_REQUEST['peer'])) {
	$peer_idx = $_REQUEST['peer'];
}

// All form save logic is in wireguard/wg.inc
if ($_POST) {
	switch ($_POST['act']) {
		case 'save':
			$res = wg_do_peer_post($_POST);
			$input_errors = $res['input_errors'];
			$pconfig = $res['pconfig'];
	
			if (empty($input_errors)) {
				if (wg_is_service_running() && $res['changes']) {
					// Everything looks good so far, so mark the subsystem dirty
					mark_subsystem_dirty($wgg['subsystems']['wg']);

					// Add tunnel to the list to apply
					wg_apply_list_add('tunnels', $res['tuns_to_sync']);
				}

				// Save was successful
				header('Location: /awg/awg_peers.php');
			}
			
			break;
		case 'genkeys':
			// Process ajax call requesting new key pair
			print(wg_gen_keypair(true));
			exit;
			break;
		case 'genpsk':
			// Process ajax call requesting new pre-shared key
			print(wg_gen_psk());
			exit;
			break;

		default:
			// Shouldn't be here, so bail out.
			header('Location: /awg/awg_peers.php');
			break;
	}
}

if (is_numericint($peer_idx) && is_array(config_get_path("installedpackages/amneziawg/peers/item/{$peer_idx}"))) {
	// Looks like we are editing an existing peer
	$pconfig = config_get_path("installedpackages/amneziawg/peers/item/{$peer_idx}");
	$is_new = false;
} else {
	// Default to enabled
	$pconfig['enabled'] = 'yes';

	// Automatically choose a tunnel based on the request 
	$pconfig['tun'] = $tun_name;

	// Default to a dynamic tunnel, so hide the endpoint form group
	$is_dynamic = true;
}


$pglinks = array("", "/awg/awg_tunnels.php", "/awg/awg_peers.php", "@self");
$active_tab = "Peers";
include('amneziawireguard/includes/awg_header.inc');
$pgtitle[]= [$active_tab,gettext("Edit")];
include("head.inc");

wg_print_service_warning();

if (!empty($input_errors)) {
	print_input_errors($input_errors);
}

display_top_tabs($tab_array);

$form = new Form(false);

$section = new Form_Section('Peer Configuration');

$form->addGlobal(new Form_Input(
	'index',
	'',
	'hidden',
	$peer_idx
));

$section->addInput(new Form_Checkbox(
	'enabled',
	'Enable',
	gettext('Enable Peer'),
	$pconfig['enabled'] == 'yes'
))->setHelp('<span class="text-danger">Note: </span>Uncheck this option to disable this peer without removing it from the list.');

$section->addInput($input = new Form_Select(
	'tun',
	'Tunnel',
	$pconfig['tun'],
	wg_get_tun_list()
))->setHelp("WireGuard tunnel for this peer. (<a href='awg_tunnels_edit.php'>Create a New Tunnel</a>)");

$section->addInput(new Form_Input(
	'descr',
	'Description',
	'text',
	$pconfig['descr'],
	['placeholder' => 'Description']
))->setHelp("Peer description for administrative reference (not parsed).");

$section->addInput(new Form_Checkbox(
	'dynamic',
	'Dynamic Endpoint',
	gettext('Dynamic'),
	empty($pconfig['endpoint']) || $is_dynamic
))->setHelp('<span class="text-danger">Note: </span>Uncheck this option to assign an endpoint address and port for this peer.');

$group = new Form_Group('Endpoint');

// Used for hiding/showing the group via JS
$group->addClass("endpoint");

$group->add(new Form_Input(
	'endpoint',
	'Endpoint',
	'text',
	$pconfig['endpoint']
))->addClass('trim')
  ->setHelp('Hostname, IPv4, or IPv6 address of this peer.<br />
	     Leave endpoint and port blank if unknown (dynamic endpoints).')
  ->setWidth(5);

$group->add(new Form_Input(
	'port',
	'Endpoint Port',
	'text',
	$pconfig['port']
))->addClass('trim')
  ->setHelp("Port used by this peer.<br />
	     Leave blank for default ({$wgg['default_port']}).")
  ->setWidth(3);

$section->add($group);

$section->addInput(new Form_Input(
	'persistentkeepalive',
	'Keep Alive',
	'text',
	$pconfig['persistentkeepalive'],
	['placeholder' => 'Keep Alive']
))->addClass('trim')
  ->setHelp('Interval (in seconds) for Keep Alive packets sent to this peer.<br />
	     Default is empty (disabled).');

$group = new Form_Group('*Peer Keys');

$group->add(new Form_Input(
	'privatekey',
	'Private Key',
	wg_secret_input_type(),
	$pconfig['privatekey'],
	['autocomplete' => 'new-password']
))->addClass('trim')
->setHelp('Private key (used when exporting the Peer Configuration)))');

$group->add(new Form_Input(
	'publickey',
	'*Public Key',
	'text',
	$pconfig['publickey']
))->addClass('trim')
->setHelp('Public key for this peer (Required). (<a id="copypubkey" style="cursor: pointer;" data-success-text="Copied" data-timeout="3000">Copy</a>)')->setReadonly();

$group->add(new Form_Button(
	'genkeys',
	'Generate',
	null,
	'fa-solid fa-key'
))->addClass('btn-primary btn-sm')
->setHelp('New Keys')
->setWidth(1);

$section->add($group);

$group = new Form_Group('Pre-shared Key');

$group->add(new Form_Input(
	'presharedkey',
	'Pre-shared Key',
	wg_secret_input_type(),
	$pconfig['presharedkey'],
	['autocomplete' => 'new-password']
))->addClass('trim')
  ->setHelp('Optional pre-shared key for this tunnel. (<a id="copypsk" style="cursor: pointer;" data-success-text="Copied" data-timeout="3000">Copy</a>)');

$group->add(new Form_Button(
	'genpsk',
	'Generate',
	null,
	'fa-solid fa-key'
))->addClass('btn-primary btn-sm')
  ->setHelp('New Pre-shared Key');

$section->add($group);

$form->add($section);

$section = new Form_Section('Address Configuration');

$section->addInput(new Form_StaticText(
	gettext('Hint'),
	gettext('Allowed IP entries here will be transformed into proper subnet start boundaries prior to validating and saving. ' .
	        'These entries must be unique between multiple peers on the same tunnel. Otherwise, traffic to the conflicting ' .
	        'networks will only be routed to the last peer in the list.')
));

// Init the addresses array if necessary
if (!is_array($pconfig['allowedips'])
    || !is_array($pconfig['allowedips']['row'])
    || empty($pconfig['allowedips']['row'])) {
		array_init_path($pconfig, 'allowedips/row/0');
	
		// Hack to ensure empty lists default to /128 mask
		$pconfig['allowedips']['row'][0]['mask'] = '128';
		if (!$is_new) {
			config_set_path("installedpackages/amneziawg/peers/item/{$peer_idx}/allowedips/row/0/mask", $pconfig['allowedips']['row'][0]['mask']);
		}
}

$last = count($pconfig['allowedips']['row']) - 1;

foreach ($pconfig['allowedips']['row'] as $counter => $item) {
	$group = new Form_Group($counter == 0 ? 'Allowed IPs' : null);

	$group->addClass('repeatable');

	$group->add(new Form_IpAddress(
		"address{$counter}",
		'Allowed Subnet or Host',
		$item['address'],
		'BOTH'
	))->addClass('trim')
	  ->setHelp($counter == $last ? 'IPv4 or IPv6 subnet or host reachable via this peer.' : '')
	  ->addMask("address_subnet{$counter}", $item['mask'], 128, 0)
	  ->setWidth(4);

	$group->add(new Form_Input(
		"address_descr{$counter}",
		'Description',
		'text',
		$item['descr']
	))->setHelp($counter == $last ? 'Description for administrative reference (not parsed).' : '')
	  ->setWidth(4);

	$group->add(new Form_Button(
		"deleterow{$counter}",
		'Delete',
		null,
		'fa-solid fa-trash-can'
	))->addClass('btn-warning btn-sm');

	$section->add($group);
}

$section->addInput(new Form_Button(
	'addrow',
	'Add Allowed IP',
	null,
	'fa-solid fa-plus'
))->addClass('btn-success btn-sm addbtn');

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
	<button type="submit" id="saveform" name="saveform" class="btn btn-primary btn-sm" value="save" title="<?=gettext('Save Peer')?>">
		<i class="fa fa-solid fa-save icon-embed-btn"></i>
		<?=gettext("Save Peer")?>
	</button>
</nav>

<?php $genkeywarning = gettext("Overwrite pre-shared key? Click 'ok' to overwrite key."); ?>

<script type="text/javascript">
//<![CDATA[
events.push(function() {
	// Supress "Delete" button if there are fewer than two rows
	checkLastRow();

	wgRegTrimHandler();

	$('#copypsk').click(function () {
		var $this = $(this);
		var originalText = $this.text();

		// The 'modern' way...
		navigator.clipboard.writeText($('#presharedkey').val());

		$this.text($this.attr('data-success-text'));

		setTimeout(function() {
			$this.text(originalText);
		}, $this.attr('data-timeout'));

		// Prevents the browser from scrolling
		return false;
	});

	// These are action buttons, not submit buttons
	$('#genpsk').prop('type','button');
	$("#genkeys").prop('type', 'button');

		// Request a new public/private key pair
	$('#genkeys').click(function(event) {
	if ($('#privatekey').val().length == 0 || confirm(<?=json_encode($genKeyWarning)?>)) {
		ajaxRequest = $.ajax({
			url: '/awg/awg_peers_edit.php',
			type: 'post',
			data: {act: 'genkeys'},
			success: function(response, textStatus, jqXHR) {
				resp = JSON.parse(response);
				$('#publickey').val(resp.pubkey);
				$('#privatekey').val(resp.privkey);
			}
		});
		}
	});

	// Request a new pre-shared key
	$('#genpsk').click(function(event) {
		if ($('#presharedkey').val().length == 0 || confirm(<?=json_encode($genkeywarning)?>)) {
			ajaxRequest = $.ajax({
				url: "/awg/awg_peers_edit.php",
				type: "post",
				data: {
					act: "genpsk"
				},
				success: function(response, textStatus, jqXHR) {
					$('#presharedkey').val(response);
				}
			});
		}
	});

	// Save the form
	$('#saveform').click(function () {
		$(form).submit();
	});

	$('#dynamic').click(function () {
		updateDynamicSection(this.checked);
	});

	function updateDynamicSection(hide) {
		hideClass('endpoint', hide);
	}

	updateDynamicSection($('#dynamic').prop('checked'));
});
//]]>
</script>

<?php
// include('amneziawireguard/includes/wg_foot.inc');
include('foot.inc');
?>
