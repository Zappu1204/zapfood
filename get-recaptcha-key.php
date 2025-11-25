<?php
/**
 * Get reCAPTCHA Site Key
 * This file provides the reCAPTCHA site key from environment variables
 * to be used in JavaScript
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/javascript');
header('Cache-Control: no-cache, must-revalidate');

echo "window.RECAPTCHA_SITE_KEY = '" . RECAPTCHA_SITE_KEY . "';";
