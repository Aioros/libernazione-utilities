function LibCookieManager() {
	var self = this;

	self.init = function() {

		self.cookiesEnabled = true;
		self.adsEnabled = true;
		self.consent = {
			given: false,
			choices: {
				social: false,
				ads: false
			}
		}

		self.checkConsent();
		self.checkCookiesEnabled();
		self.checkAdsEnabled();
	}

	self.checkConsent = function() {
		var libCookie = document.cookie.replace(/(?:(?:^|.*;\s*)libcookie\s*\=\s*([^;]*).*$)|^.*$/, "$1");
		if (libCookie != "") {
			self.consent.given = true;
			self.consent.choices = JSON.parse(libCookie);
		}
	}

	self.checkCookiesEnabled = function() {
		var cookieEnabled = (navigator.cookieEnabled) ? true : false;
	    if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled) { 
	        document.cookie = "testcookie";
	        cookieEnabled = (document.cookie.indexOf("testcookie") != -1) ? true : false;
	        document.cookie = "testcookie=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
	    }
	    self.cookiesEnabled = cookieEnabled;
	}

	self.checkAdsEnabled = function() {
		// already checked by ads.js
		self.adsEnabled = typeof canRunAds !== "undefined";
	}

	self.processConsent = function(consent) {
		self.consent.choices = consent;
		document.cookie = "libcookie=" + JSON.stringify(self.consent.choices);
		location.reload();
	}

	self.proceed = function() {
		if (self.consent.choices.social) {
			libStartSocial();
		}
		if (self.consent.choices.ads) {
			head.js({"lazyad": "http://libernazione.it/wp-content/plugins/libernazione-utils/js/lazyad-loader.min.js"});
		}
	}

}

(function() {
	var $ = jQuery;

	var libCookies = new LibCookieManager();
	libCookies.init();

	var socialCheck = $("#consent_social");
	var adsCheck = $("#consent_ads");
	var consentButton = $("button.consent")

	if (libCookies.consent.given) {
		socialCheck.prop("checked", libCookies.consent.choices.social);
	} else {
		socialCheck.prop("checked", true);
	}

	if (libCookies.adsEnabled) {
		if (libCookies.consent.given) {
			adsCheck.prop("checked", libCookies.consent.choices.ads);
		} else {
			adsCheck.prop("checked", true);
		}
	} else {
		adsCheck.prop("disabled", true);
		$("#text_pub").html("<s>" + $("#text_pub").text() + "</s><br>(inserzioni bloccate da un ad blocker)");
	}

	if (!libCookies.cookiesEnabled) {
		// The user is managing cookies on his own pretty well, we're free to go
		libCookies.consent.given = true;
		libCookies.consent.choices.social = true;
		libCookies.consent.choices.ads = true;
		socialCheck.prop("checked", false).prop("disabled", true);
		adsCheck.prop("checked", false).prop("disabled", true);
		consentButton.prop("disabled", true);

		$("#text_social").html("<s>" + $("#text_social").text() + "</s><br>(cookies già bloccati dal browser)");
		$("#text_pub").html("<s>" + $("#text_pub").text() + "</s><br>(cookies già bloccati dal browser)");
	}

	consentButton.click(function() {
		var consent = {
			social: $("#consent_social").get(0).checked,
			ads: $("#consent_ads").get(0).checked,
		};
		libCookies.processConsent(consent);
		return false;
	});

	$("#cookie_options").click(function() {
		$("#libcookies_small").hide();
		$("#libcookies").show();
		return false;
	});

	$("#libcookies .fa-close").click(function() {
		$("#libcookies").hide();
		$("#libcookies_small").show();
	});

	if (!libCookies.consent.given) {
		
		$("#libcookies").show();

	} else {

		$("#libcookies_small").show();
		libCookies.proceed();

	}

})();