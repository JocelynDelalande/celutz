function link_file_to_input(file_input, text_input) {
	file_input.onchange = function(e) {
		// remove the extension, that's a good default title :-)
		text_input.value = e.target.value.replace(/\.[^/.]+$/, "");
	};
}

