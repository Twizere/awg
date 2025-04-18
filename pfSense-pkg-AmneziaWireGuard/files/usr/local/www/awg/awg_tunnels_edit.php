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

$pconfig = [];

// Always assume we are creating a new tunnel
$is_new = true;

if (isset($_REQUEST['tun'])) {
	$tun = $_REQUEST['tun'];
	$tun_idx = wg_tunnel_get_array_idx($_REQUEST['tun']);
}

if ($_POST) {
	if (isset($_POST['apply'])) {
		$ret_code = 0;

		if (is_subsystem_dirty($wgg['subsystems']['wg'])) {
			if (wg_is_service_running() && wg_is_service_enabled()  ) {
				$tunnels_to_apply = wg_apply_list_get('tunnels');
				$sync_status = wg_tunnel_sync($tunnels_to_apply, true, true);
				$ret_code |= $sync_status['ret_code'];
			}

			if ($ret_code == 0) {
				clear_subsystem_dirty($wgg['subsystems']['wg']);
			}
		}
	}

	if (isset($_POST['act'])) {
		switch ($_POST['act']) {
			case 'save':
				$res = wg_do_tunnel_post($_POST);
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
					header('Location: /awg/awg_tunnels.php');
				}

				break;

			case 'genkeys':
				// Process ajax call requesting new key pair
				print(wg_gen_keypair(true));
				exit;
				break;

			case 'genpubkey':
				// Process ajax call calculating the public key from a private key
				print(wg_gen_publickey($_POST['privatekey'], true));
				exit;
				break;

			default:
				// Shouldn't be here, so bail out.
				header('Location: /awg/awg_tunnels.php');
				break;
		}
	}

	if (isset($_POST['peer'])) {
		$peer_idx = $_POST['peer'];
		switch ($_POST['act']) {
			case 'toggle':
				$res = wg_toggle_peer($peer_idx);
				break;

			case 'delete':
				$res = wg_delete_peer($peer_idx);
				break;

			default:
				// Shouldn't be here, so bail out.
				header('Location: /awg/awg_tunnels.php');
				break;
		}

		$input_errors = $res['input_errors'];

		if (empty($input_errors)) {
			if (wg_is_service_running() && $res['changes']) {
				mark_subsystem_dirty($wgg['subsystems']['wg']);

				// Add tunnel to the list to apply
				wg_apply_list_add('tunnels', $res['tuns_to_sync']);
			}
		}
	}
}

// A dirty string hack
$s = fn($x) => $x;

// Looks like we are editing an existing tunnel
if (is_numericint($tun_idx) && is_array(config_get_path("installedpackages/amneziawg/tunnels/item/{$tun_idx}"))) {
	$pconfig = config_get_path("installedpackages/amneziawg/tunnels/item/{$tun_idx}");

	// Supress warning and allow peers to be added via the 'Add Peer' link
	$is_new = false;
// Looks like we are creating a new tunnel
} else {
	// Default to enabled
	$pconfig['enabled'] = 'yes';
	$pconfig['name'] = next_wg_if();
}

// Save the MTU settings prior to re(saving)
$pconfig['mtu'] = get_interface_mtu($pconfig['name']);
if (!$is_new) {
	config_set_path("installedpackages/amneziawg/tunnels/item/{$tun_idx}/mtu", $pconfig['mtu']);
}



$pglinks = array("", "/awg/wg_tunnels.php", "/awg/wg_tunnels.php", "@self");
$active_tab = "Tunnels";
include('amneziawireguard/includes/awg_header.inc');
$pgtitle[] = [$active_tab,gettext("Edit")];
include("head.inc");

wg_print_service_warning();

if (isset($_POST['apply'])) {
	print_apply_result_box($ret_code);
}

wg_print_config_apply_box();

if (!empty($input_errors)) {
	print_input_errors($input_errors);
}

display_top_tabs($tab_array);

$form = new Form(false);

$section = new Form_Section("Tunnel Configuration ({$pconfig['name']})");

$form->addGlobal(new Form_Input(
	'index',
	'',
	'hidden',
	$tun_idx
));

$tun_enable = new Form_Checkbox(
	'enabled',
	'Enable',
	gettext('Enable Tunnel'),
	$pconfig['enabled'] == 'yes'
);

$tun_enable->setHelp('<span class="text-danger">Note: </span>Tunnel must be <b>enabled</b> in order to be assigned to a pfSense interface.');	

// Disable the tunnel enabled button if interface is assigned in pfSense
if (is_wg_tunnel_assigned($pconfig['name'])) {
	$tun_enable->setDisabled();
	$tun_enable->setHelp('<span class="text-danger">Note: </span>Tunnel cannot be <b>disabled</b> when assigned to a pfSense interface.');

	// We still want to POST this field, make it a hidden field now
	$form->addGlobal(new Form_Input(
		'enabled',
		'',
		'hidden',
		'yes'
	));
}

$section->addInput($tun_enable);

$section->addInput(new Form_Input(
	'descr',
	'Description',
	'text',
	$pconfig['descr'],
	['placeholder' => 'Description']
))->setHelp('Description for administrative reference (not parsed).');

$section->addInput(new Form_Input(
	'listenport',
	'*Listen Port',
	'text',
	$pconfig['listenport'],
	['placeholder' => next_wg_port(), 'autocomplete' => 'new-password']
))->addClass('trim')
  ->setHelp('Port used by this tunnel to communicate with peers.');

$group = new Form_Group('*Interface Keys');

$group->add(new Form_Input(
	'privatekey',
	'Private Key',
	wg_secret_input_type(),
	$pconfig['privatekey'],
	['autocomplete' => 'new-password']
))->addClass('trim')
  ->setHelp('Private key for this tunnel. (Required)');

$group->add(new Form_Input(
	'publickey',
	'Public Key',
	'text',
	$pconfig['publickey']
))->addClass('trim')
  ->setHelp('Public key for this tunnel. (<a id="copypubkey" style="cursor: pointer;" data-success-text="Copied" data-timeout="3000">Copy</a>)')->setReadonly();

$group->add(new Form_Button(
	'genkeys',
	'Generate',
	null,
	'fa-solid fa-key'
))->addClass('btn-primary btn-sm')
  ->setHelp('New Keys')
  ->setWidth(1);

$section->add($group);

$form->add($section);
$section = new Form_Section('AmneziaWireGuard Options');
$group = new Form_Group('Junk Packet Options');

$group->add(new Form_Input(
	'jc',
	'Jc (Junk packet count)',
	'text',
	$pconfig['jc'] ?? '',
	['placeholder' => 'Jc']
))->setHelp('Number of random packets before the session starts. Recommended: 3-10.')
->setWidth(2);


$group->add(new Form_Input(
	'jmin',
	'Jmin (Junk packet minimum size)',
	'text',
	$pconfig['jmin'] ?? '',
	['placeholder' => 'Jmin']
))->setHelp('Minimum size for Junk packets. Recommended: 100.');

$group->add(new Form_Input(
	'jmax',
	'Jmax (Junk packet maximum size)',
	'text',
	$pconfig['jmax'] ?? '',
	['placeholder' => 'Jmax']
))->setHelp('Maximum size for Junk packets. Recommended: 1000.');

$group->add(new Form_Button(
	'gen_junk_packet_options',
	'Generate',
	null,
	'fa-solid fa-cog'
))->addClass('btn-primary btn-sm')
  ->setWidth(1);

$section->add($group);

$group = new Form_Group('Packet Junk Sizes');

$group->add(new Form_Input(
	's1',
	'S1 (Init packet junk size)',
	'text',
	$pconfig['s1'] ?? '',
	['placeholder' => 'S1']
))->setHelp('Size of random data in the init packet. Recommended: 50-500.');

$group->add(new Form_Input(
	's2',
	'S2 (Response packet junk size)',
	'text',
	$pconfig['s2'] ?? '',
	['placeholder' => 'S2']
))->setHelp('Size of random data in the response packet. Recommended: 50-500.');

$group->add(new Form_Button(
	'gen_packet_junk_sizes',
	'Generate',
	null,
	'fa-solid fa-cog'
))->addClass('btn-primary btn-sm')
  ->setWidth(1);

$section->add($group);

$group = new Form_Group('Magic Headers');

$group->add(new Form_Input(
	'h1',
	'H1 (Init packet magic header)',
	'text',
	$pconfig['h1'] ?? '',
	['placeholder' => 'H1']
))->setHelp('Header for the first byte of the handshake. Recommended: Random < uint_max.');

$group->add(new Form_Input(
	'h2',
	'H2 (Response packet magic header)',
	'text',
	$pconfig['h2'] ?? '',
	['placeholder' => 'H2']
))->setHelp('Header for the first byte of the handshake response. Recommended: Random < uint_max.');

$group->add(new Form_Input(
	'h3',
	'H3 (Underload packet magic header)',
	'text',
	$pconfig['h3'] ?? '',
	['placeholder' => 'H3']
))->setHelp('Header for the UnderLoad packet. Recommended: Random < uint_max.');

$group->add(new Form_Input(
	'h4',
	'H4 (Transport packet magic header)',
	'text',
	$pconfig['h4'] ?? '',
	['placeholder' => 'H4']
))->setHelp('Header for the transmitted data packet. Recommended: Random < uint_max.');

$group->add(new Form_Button(
	'gen_magic_headers',
	'Generate',
	null,
	'fa-solid fa-cog'
))->addClass('btn-primary btn-sm')
  ->setWidth(1);

$section->add($group);

$form->add($section);


$section = new Form_Section("Interface Configuration ({$pconfig['name']})");

$section->setAttribute('id', 'addresses');

if (!is_wg_tunnel_assigned($pconfig['name'])) {
	$section->addInput(new Form_StaticText(
		'Assignment',
		"<i class='fa fa-solid fa-sitemap' style='vertical-align: middle;'></i><a style='padding-left: 3px' href='/interfaces_assign.php'>Interface Assignments</a>"
	));

	$section->addInput(new Form_StaticText(
		'Firewall Rules',
		"<i class='fa fa-solid fa-shield-alt' style='vertical-align: middle;'></i><a style='padding-left: 3px' href='/firewall_rules.php?if={$wgg['ifgroupentry']['ifname']}'>WireGuard Interface Group</a>"
	));

	$section->addInput(new Form_StaticText(
		'Hint',
		"These interface addresses are only applicable for unassigned WireGuard tunnel interfaces.</a>"
	));

	// Init the addresses array if necessary
	if (!is_array($pconfig['addresses'])
	    || !is_array($pconfig['addresses']['row'])
	    || empty($pconfig['addresses']['row'])) {
			array_init_path($pconfig, 'addresses/row/0');

			// Hack to ensure empty lists default to /128 mask
			$pconfig['addresses']['row'][0]['mask'] = '128';
			if (!$is_new) {
				config_set_path("installedpackages/amneziawg/tunnels/item/{$tun_idx}/addresses/row/0/mask", $pconfig['addresses']['row'][0]['mask']);
			}
		}

	$last = count($pconfig['addresses']['row']) - 1;

	foreach ($pconfig['addresses']['row'] as $counter => $item) {
		$group = new Form_Group($counter == 0 ? 'Interface Addresses' : '');

		$group->addClass('repeatable');

		$group->add(new Form_IpAddress(
			"address{$counter}",
			'Interface Address',
			$item['address'],
			'BOTH'
		))->addClass('trim')
		  ->setHelp($counter == $last ? 'IPv4 or IPv6 address assigned to the tunnel interface.' : '')
		  ->addMask("address_subnet{$counter}", $item['mask'])
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
		'Add Address',
		null,
		'fa-solid fa-plus'
	))->addClass('btn-success btn-sm addbtn');
} else {
	$wg_pfsense_if = wg_get_pfsense_interface_info($pconfig['name']);

	$section->addInput(new Form_StaticText(
		'Assignment',
		"<i class='fa fa-solid fa-sitemap' style='vertical-align: middle;'></i><a style='padding-left: 3px' href='/interfaces_assign.php'>{$s(htmlspecialchars($wg_pfsense_if['descr']))} ({$s(htmlspecialchars($wg_pfsense_if['name']))})</a>"
	));

	$section->addInput(new Form_StaticText(
		'Interface',
		"<i class='fa fa-solid fa-ethernet' style='vertical-align: middle;'></i><a style='padding-left: 3px' href='/interfaces.php?if={$s(htmlspecialchars($wg_pfsense_if['name']))}'>{$s(gettext('Interface Configuration'))}</a>"
	));

	$section->addInput(new Form_StaticText(
		'Firewall Rules',
		"<i class='fa fa-solid fa-shield-alt' style='vertical-align: middle;'></i><a style='padding-left: 3px' href='/firewall_rules.php?if={$s(htmlspecialchars($wg_pfsense_if['name']))}'>{$s(gettext('Firewall Configuration'))}</a>"
	));
}

$form->add($section);

$form->addGlobal(new Form_Input(
	'mtu',
	'',
	'hidden',
	$pconfig['mtu']
));

$form->addGlobal(new Form_Input(
	'is_new',
	'',
	'hidden',
	$is_new
));

$form->addGlobal(new Form_Input(
	'act',
	'',
	'hidden',
	'save'
));

print($form);

?>

<div class="panel panel-default">
	<div class="panel-heading">
		<h2 class="panel-title"><?=gettext('Peer Configuration')?></h2>
	</div>
	<div id="mainarea" class="table-responsive panel-body">
		<table id="peertable" class="table table-hover table-striped table-condensed" style="overflow-x: visible;">
			<thead>
				<tr>
					<th><?=gettext('Description')?></th>
					<th><?=gettext('Public key')?></th>
					<th><?=gettext('Tunnel')?></th>
					<th><?=gettext('Allowed IPs')?></th>
					<th><?=htmlspecialchars(wg_format_endpoint(true))?></th>
					<th><?=gettext('Actions')?></th>
				</tr>
			</thead>
			<tbody>
<?php
	if (!$is_new):
		foreach (wg_tunnel_get_peers_config($pconfig['name']) as [$peer_idx, $peer, $is_new]):
?>
				<tr ondblclick="document.location='<?="awg_peers_edit.php?peer={$peer_idx}"?>';" class="<?=wg_peer_status_class($peer)?>">
					<td><?=htmlspecialchars($peer['descr'])?></td>
					<td title="<?=htmlspecialchars($peer['publickey'])?>">
						<?=htmlspecialchars(substr($peer['publickey'], 0, 16).'...')?>
					</td>
					<td><?=htmlspecialchars($peer['tun'])?></td>
					<td><?=wg_generate_peer_allowedips_popup_link($peer_idx)?></td>
					<td><?=htmlspecialchars(wg_format_endpoint(false, $peer))?></td>
					<td style="cursor: pointer;">
						<a class="fa fa-solid fa-pencil" title="<?=gettext('Edit Peer')?>" href="<?="awg_peers_edit.php?peer={$peer_idx}"?>"></a>
						<?=wg_generate_toggle_icon_link(($peer['enabled'] == 'yes'), 'peer', "?act=toggle&peer={$peer_idx}&tun={$tun}")?>
						<a class="fa fa-solid fa-trash-can text-danger" title="<?=gettext('Delete Peer')?>" href="<?="?act=delete&peer={$peer_idx}&tun={$tun}"?>" usepost></a>
					</td>
				</tr>

<?php
		endforeach;
	else:
?>
				<tr>
					<td colspan="6">
						<?php print_info_box('New tunnels must be saved before adding or assigning peers.', 'warning', null); ?>
					</td>
				</tr>
<?php
	endif;
?>
			</tbody>
		</table>
	</div>
</div>

<nav class="action-buttons">
<?php
// We cheat here and show disabled buttons for a better user experience
if ($is_new):
?>
	<button class="btn btn-success btn-sm" title="<?=gettext('Add Peer')?>" disabled>
		<i class="fa fa-solid fa-plus icon-embed-btn"></i>
		<?=gettext('Add Peer')?>
	</button>
<?php
// Now we show the actual links once the tunnel is actually saved
else:
?>
	<a href="<?="awg_peers_edit.php?tun={$pconfig['name']}"?>" class="btn btn-success btn-sm">
		<i class="fa fa-solid fa-plus icon-embed-btn"></i>
		<?=gettext('Add Peer')?>
	</a>
<?php
endif;
?>
	<button type="submit" id="saveform" name="saveform" class="btn btn-primary btn-sm" value="save" title="<?=gettext('Save tunnel')?>">
		<i class="fa fa-solid fa-save icon-embed-btn"></i>
		<?=gettext('Save Tunnel')?>
	</button>
</nav>

<?php $genKeyWarning = gettext("Overwrite key pair? Click 'ok' to overwrite keys."); ?>

<script type="text/javascript">
//<![CDATA[
events.push(function() {
	// Supress "Delete" button if there are fewer than two rows
	checkLastRow();

	// wgRegTrimHandler();

	$('#copypubkey').click(function () {
		var $this = $(this);
		var originalText = $this.text();

		try {
			// The 'modern' way, this only works with https
			navigator.clipboard.writeText($('#publickey').val());
		} catch {
			console.warn("Failed to copy text using navigator.clipboard, falling back to commands");
			$('#publickey').select();
			document.execCommand("copy");
		}

		$this.text($this.attr('data-success-text'));

		setTimeout(function() {
			$this.text(originalText);
		}, $this.attr('data-timeout'));

		// Prevents the browser from scrolling
		return false;
	});

	// These are action buttons, not submit buttons
	$("#genkeys").prop('type', 'button');
	$('#gen_junk_packet_options').prop('type', 'button');
	$('#gen_packet_junk_sizes').prop('type', 'button');
	$('#gen_magic_headers').prop('type', 'button');


	// Request a new public/private key pair
	$('#genkeys').click(function(event) {
		if ($('#privatekey').val().length == 0 || confirm(<?=json_encode($genKeyWarning)?>)) {
			ajaxRequest = $.ajax({
				url: '/awg/awg_tunnels_edit.php',
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

	// Request a new public key when private key is changed
	$('#privatekey').change(function(event) {
		ajaxRequest = $.ajax(
			{
				url: '/awg/awg_tunnels_edit.php',
				type: 'post',
				data: {
					act: 'genpubkey',
					privatekey: $('#privatekey').val()
				},
			success: function(response, textStatus, jqXHR) {
				resp = JSON.parse(response);
				$('#publickey').val(resp.pubkey);
			}
		});
	});


	// Generate default values for Junk Packet Options
	$('#gen_junk_packet_options').click(function(event) {
		$('#jc').val(Math.floor(Math.random() * 125) + 3);
		$('#jmin').val(Math.floor(Math.random() * 690) + 10);
		let jmin = parseInt($('#jmin').val());
		$('#jmax').val(Math.floor(Math.random() * 570) + jmin + 1);
	});

	// Generate default values for Packet Junk Sizes
	$('#gen_packet_junk_sizes').click(function(event) {
		$('#s1').val(Math.floor(Math.random() * 124) + 3);
		$('#s2').val(Math.floor(Math.random() * 124) + 3);
	});

	// Generate default values for Magic Headers
	$('#gen_magic_headers').click(function(event) {
		let min = 0x10000011;
		let max = 0x7FFFFF00;
		$('#h1').val(Math.floor(Math.random() * (max - min)) + min);
		$('#h2').val(Math.floor(Math.random() * (max - min)) + min);
		$('#h3').val(Math.floor(Math.random() * (max - min)) + min);
		$('#h4').val(Math.floor(Math.random() * (max - min)) + min);
	});

	// Save the form
	$('#saveform').click(function(event) {
	$(form).submit();
	});

});
//]]>
</script>

<?php
// include('amneziawireguard/includes/wg_foot.inc');
include('foot.inc');
?>
