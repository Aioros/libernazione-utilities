<?php

add_action("wp_enqueue_scripts", "lib_cookie_scripts");
function lib_cookie_scripts() {
	if (!is_admin()) {
		wp_enqueue_script("lib-detect-adblock", plugin_dir_url( __FILE__ ) . "js/ads.js", array(), "1.0", true);
		wp_enqueue_script("lib-cookies", plugin_dir_url( __FILE__ ) . "js/libcookies.js", array("jquery", "lib-detect-adblock"), "1.0", true);

		wp_enqueue_style("lib-cookies", plugin_dir_url( __FILE__ ) . "css/libcookies.css");
	}
}

add_action("wp_footer", "lib_cookie_banner");
function lib_cookie_banner() {
	?>
	<div id="libcookies" style="display: none;">
		<span class="fa fa-close"></span>
		<div class="message">Libernazione prende sul serio la tua privacy, ma anche e soprattutto l'irrazionale legge sui cookie
			che si propone di proteggerla. Oltre ad alcuni cookie tecnici necessari al suo funzionamento, questo sito usa cookie
			di profilazione di terze parti: non verranno installati prima di aver avuto il consenso, ma in loro assenza alcune
			funzionalità potrebbero non essere disponibili. Puoi avere informazioni dettagliate sui cookie
			leggendo la <a href="/privacy">Privacy Policy</a>.
		</div>
		<form>
			<div class="choices">
				<input type="checkbox" id="consent_social">
				<label for="consent_social">Accetto i cookie per funzionalità social</label>
				<br>
				<input type="checkbox" id="consent_ads">
				<label for="consent_ads">Accetto i cookie delle inserzioni pubblicitarie</label>
			</div>
			<button class="consent">Invia preferenze</button>
		</form>
	</div>
	<div id="libcookies_small" style="display: none;">
		<a href="#" id="cookie_options">Opzioni cookie</a>&nbsp;&nbsp;·&nbsp;&nbsp;<a href="/privacy">Privacy Policy</a>
	</div>
	<?php
}