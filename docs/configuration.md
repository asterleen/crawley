# Crawley Installation & Configuration
## Installing Crawley
### Prerequisites
Crawley is written in PHP, so a PHP interpreter with additional modules is required. PHP is best ran with Apache web server but nginx is fine too. Let's assume that you already have Apache server and PHP installed on Ubuntu/Debian server. You may need to install additional modules:
```
apt install php-curl php-mbstring php-pgsql
```

Crawley uses PostgreSQL database to save the posts. MySQL/MariaDB support will be implemented if it will requested by Community. Install PostgreSQL using this:
```
apt install postgresql
```

That's all. Now we have to set up our software.
### Web Server
We hope that you have already set up your Apache setup. You can use defaults but you have to set `AllowOverride all` for the `VirtualHost` where Crawley will be used with.

### Database
OK, PostgreSQL may be complicated, especially when you just installed it. So, let's do it together!  
First, switch to `postgres` account using this command as root:
```
su - postgres
```
Then connect to your database (run this as `postgres`):
```
psql
```
Next, it's recommended to have a separate user for every service. Let's create new user:
```
CREATE USER crawley WITH ENCRYPTED PASSWORD 'hackme';
```
Don't forget to replace `hackme` with your own password. Save it, you will need it later! Okay, next let's create a new database for Crawley:
```
CREATE DATABASE crawley_db;
ALTER DATABASE crawley_db OWNER TO crawley;
```
Now exit from `postgres` user with `\q` command. All the next actions with the database we will perform as `crawley` user. To do that, you need to allow `crawley` to connect to the database. Open file ` /etc/postgresql/x.y/main/pg_hba.conf`m where `x.y` is your version of PostgreSQL. Edit this file and add this:
```
host web crawley 127.0.0.1/32  md5
```
Now download [this SQL file](https://raw.githubusercontent.com/asterleen/crawley/master/dist/crawley.pg.sql) to `/tmp` for example and run this to create all neccessary tables and sequences:
```
wget -O /tmp/crawley.pg.sql https://raw.githubusercontent.com/asterleen/crawley/master/dist/crawley.pg.sql
psql -U crawley -d crawley_db -f /tmp/crawley.pg.sql --password
```
That's it! You database is ready. Let's go ahead.

### Installation
It's easy as hell. Just download Crawley and copy the contents of `src` folder into your web server directory. Copy or rename `engine/enconfig.sample.php` to `engine/enconfig.php`, edit it and you're done! 

## Configuring Crawley
### Setting up the Core
Crawley's Core is configured by editing `engine/enconfig.php` file. It contains constants that are used by Crawley to connect to the database, what paths to use to save the attachments, who is the admin of this instance and so on. Detailed description of each parameter in `enconfig.php` is described in [enconfig.md document](enconfig.md). To start configuring Crawley you must set up these values:
- `DB_` prefixed key determine connection to the database.
- `TELEGRAM_CONTENT_SAVE_PATH` defines the path where Crawley will save attachments. This directory must be writable and accessible by the web server. It's highly recommended to disable PHP execution here. Crawley will create the needed structure. This is an absolute path in your filesystem and should look like `/srv/www/crawley/content`.
- `CONTENT_URL_PREFIX` must point to the place where attachments will be accessible from the web. This parameter is a part of the URL, so it must look like `https://example.com/crawley/content`.
- `TELEGRAM_BOT_TOKEN` is the token that is given to you by @[BotFather](https://t.me/botfather). Please read the official Telegram [documentation](https://core.telegram.org/bots#6-botfather) on creating and managing bots.

Now you have to connect Crawley and the Telegram servers. Crawley uses WebHooks, so you need to set up a webhook that will point at the web server where Crawley runs at. Let's assume that Crawley is accessible at `https://example.com/crawley` and the bot token is `110201543:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw`. 

- `TELEGRAM_CALLBACK_KEY` is the internal key that is used to ensure that the request is came from Telegram servers. Set it random.  

OK, let your callback key be `_this_is_my_key_`. Then your webhook URL will look like this:
```
https://example.com/crawley/callback/_this_is_my_key_
```
So let's tell Telegram about it:
```
https://api.telegram.org/bot110201543:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw/setWebhook?url=https://example.com/crawley/callback/_this_is_my_key_
```
Telegram should respond with something like `Webhook was set`. Now you can interact with your bot via Telegram! Let's obtain your User ID to continue configuring Crawley. just send the `/whoami` command to your bot and it will respond you with your ID. Use it in `TELEGRAM_UBER_ADMIN_UID` parameter.

- `TELEGRAM_UBER_ADMIN_UID` is your Telegram user ID. Crawley will authorize you by this ID. 

You're done with core configuration! Other values are optional and can be safely left in their default state.
### Connecting Crawley with your channel
Just follow these steps and you'll get done!

1. **Disable privcay mode for your bot**. Open `BotFather`, select your bot, select `Privacy Mode` and set it to **off**.
2. **Add the bot to your channel**. Open your chat, select `Manage Channel`, then `Administrator`, click `Add Administrator`, search for your bot by its @-name, then click `OK`. It will ask you for permissions for this bot. Switch on `Post`, `Edit` and `Delete messages`, switch off the other options.
3. **Get an addition key**. It's sort of securituy feature of Crawley to prevent others from switching Crawley to their channels. Send `/getkey` command to Crawley bot in private messages. It will respond with an addition key. 
4. **Connect Crawley to your channel**. Send a `/setchat` command with the key just to your channel. Don't be afraid, Crawley will delete this message after successful addition!
5. You're done! Now Crawley will record all your messages into its database and tehy can be used on your website with Crawley's API.

## Further reading
After you configured your Crawley bot you can read how to use it in [user's guide](usage.md).