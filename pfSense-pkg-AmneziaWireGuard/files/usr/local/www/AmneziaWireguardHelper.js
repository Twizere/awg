

function wgRegTrimHandler() {
	$('body').on('change', '.trim', function () {
		$(this).val($(this).val().replace(/\s/g, ''));
	});
}
