<?php

namespace P4EN\Scraper;

class ENSelectors {

	const FORM_FIELDS_XPATH = "//form[contains(@class, 'en__component')]//*[not(contains(@style, 'display: none'))]/*[contains(@class, 'en__field__input') and not(@disabled)]";
	const ALL_FORM_FIELDS_XPATH = "//form[contains(@class, 'en__component')]//*[contains(@class, 'en__field__input')]";

}
