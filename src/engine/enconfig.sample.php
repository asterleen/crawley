<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

// Fill in the config params and rename me to enconfig.php

// Database
define ('DB_HOST', 'localhost');
define ('DB_NAME', 'crawley');
define ('DB_USER', 'crawley');
define ('DB_PASSWORD', '_hack_me_plz_');

// Telegram
define ('TELEGRAM_BOT_TOKEN', ''); // Obtain in from @BotFather
define ('TELEGRAM_CALLBACK_KEY', ''); // This will be used in your webhook
define ('TELEGRAM_USE_DIRECT_RESPONSE', true); // respond directly or use HTTP API
define ('TELEGRAM_CONTENT_SAVE_PATH', '/srv/www/crawley/content'); // no trailing slash!
define ('TELEGRAM_UBER_ADMIN_UID', 313371488); // Your Telegram internal ID, Crawley will accept commands only from you

define ('CONTENT_URL_PREFIX', 'https://crawley.example.com/content'); // no trailing slash!