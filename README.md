# Crawley the Telegram Beholder
Hi, this is Crawley!
Crawley allows you to use your Telegram channel as a news feed for your website. Crawley is a standalone, independent from other libraries Telegram bot written in PHP. It can be used on both dedicated and shared hostings. Crawley has an API to get saved posts in JSON format and use it on your website. 

## Prerequisites
`Apache` web server is the preferable because of its support of `.htaccess`. Don't forget to set up the `AllowOverride all` configuration option. Alternatively you can set up the `nginx` server with corresponding rewrite options, see `.htaccess` file in `/src/` directory. It is also neccessary to restrict web server's clients from accessing the `engine` directory.

Crawley is tested with PHP7, but it does not use any of PHP7-specific functions. The following PHP modules are required: `curl`, `pgsql`, `mbstring`. 
In Ubuntu/Debian run this command as root:  
```apt install php-curl php-mbstring php-pgsql```  

We use PostgreSQL as the database so you also will need to set it up. MySQL/MariaDB will be added later or on your demand.  

That's all! We don't want anything special from you.

## Installation
0. Make sure that your setup meets the prerequisites
1. Download Crawley and upload the contents of `src` directory onto your web server
2. Execute the SQL from `dist/crawley.pg.sql` on your database (privileges and schemas are on your own)
3. Proceed to configuration section!

## Configuration
### Basic setup
If you don't have a Telegram bot yet, obtain it from [BotFather](https://teleg.run/BotFather). It will give you an unique bot ID:Key, remember it. Disable "Privacy Mode" for your bot to allow it to access the messages of your channel.
After that, copy `engine/enconfig.sample.php` to `engine/enconfig.php` and edit it according to your requirements. Detailed explanation of the configuration fields is [here](docs/enconfig.md). Take your attention at the `TELEGRAM_CALLBACK_KEY` configuration option. It should be random and it will be used in Telegram's webhook to make sure that it's really the Telegram server came to Crawley.

### Telegram connection
Let's assume that Crawley is accessible at `https://example.com/crawley`. Using the Telegram API make a request to register the webhook pointing to Crawley, like this:
```http://api.telegram.org/bot<num>:<key>/setWebhook?url=https://example.com/crawley/callback/<your_callback_key>```
Telegram should respond with the `OK` status and `Webhook was set` comment. 

### Connecting Crawley with your channel
**First**, determine your user ID. To do that, send a `/whoami` command to Crawley and it will respond you with your Telegram user ID. Edit the `engine/enconfig.php` file and set the `TELEGRAM_UBER_ADMIN_UID` to the value you got from Crawley.  
**Second**, add Crawley to your channel. In Channel Settings choose 'Add Administrators', find your bot and add it to the channel. Allow it to publish, edit and delete messages, other privileges can (and should) be disabled.  
**Third**, send a `/getkey` command to Crawley in private messages. It will respond you with a key to be used to connect your channel. Go to your channel and send `/setchat` command with the key provided by Crawley. Don't worry, after setting up Crawley will remove this message.  
**That's it!** You are able now to post records to the Telegram channel and they will be copied to Crawley's database.  

### Moar configuration
Detailed configuration steps are described in [configuration.md](docs/configuration.md) document.