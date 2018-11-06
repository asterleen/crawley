# Crawley Configuration file

The `enconfig.php` file describes the core configuration of Crawley. It contains the database connection credentials, paths and other neccessary information. The table below describes every key as detailed as possible.

|  Configuration key | Description  |
| ------------ | ------------ |
| `CONTENT_URL_PREFIX` | Used to determine your domain name and path. Crawley sends full URLs in API when sending the posts |
| `CONTENT_MAX_AMOUNT` | Maximal amount of posts that can be obtained via API |
| `CONTENT_DEFAULT_AMOUNT` | Default posts amount for the API |
| `CORS_ALLOW_EXTERNAL` | Allow websites perform CORS requests to Crawley |
| `DB_HOST` | Database hostname |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASSWORD` | Password to access the database |
| `TELEGRAM_BOT_TOKEN` | Token that BotFather gives you when you create the bot. Looks like `31337:AAhiGchKJf-aAAaaaGGGKFKKF-AAfSFLVUWBCOL` |
| `TELEGRAM_CALLBACK_KEY` | Internal callback key. Used to ensure that it's really Telegram's request. Make random |
| `TELEGRAM_USE_DIRECT_RESPONSE` | When set to `true` (by default), Crawley will respond with JSON to Telegram server and die, otherwise, a HTTP request to Telegram servers will be performed. Preferrable to leave `true`. |
| `TELEGRAM_CONTENT_SAVE_PATH` | Determines where to store the attachments from the posts. Must be writable, highly recommended to isolate it from PHP. |
| `TELEGRAM_UBER_ADMIN_UID` | Your Telegram user ID. It's used by Crawley restrict stray users from control commands |