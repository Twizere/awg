<?php

$pgtitle = array("VPN", "Amenezia WG");
$shortcut_section = "ameneziawg";
$tab_array = array();
$tab_array[] = array(gettext("Status"), $active_tab == "Status", "/awg/status_amneziawg.php");
$tab_array[] = array(gettext("Tunnels"), $active_tab == "Tunnels", "/awg/awg_tunnels.php");
$tab_array[] = array(gettext("Peers"), $active_tab == "Peers", "/awg/awg_peers.php");
$tab_array[] = array(gettext("Settings"), $active_tab == "Settings", "/awg/awg_settings.php");
$tab_array[] = array(gettext("API"), $active_tab == "Api", "/awg/awg_api.php");
add_package_tabs("AWG", $tab_array);

?>
<script>
	function wgRegTrimHandler() {
		$('body').on('change', '.trim', function () {
			$(this).val($(this).val().replace(/\s/g, ''));
		});
	}
</script>
