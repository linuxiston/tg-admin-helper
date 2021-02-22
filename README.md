Telegram bot for help group admins  
-------
Telegram bot for managing [Linuxiston Community Group](https://t.me/linuxiston)

REQUIREMENTS
----

The minimum requirement by this project that your Web server supports PHP 7.1.0

INSTALLATION
------------
- Setup your bot to public folder  
example: api.telegram.org/botTOKEN/setwebhook?url=https://yourdomain.com/public
- add bot as admin
- edit `/config/config.php`
```php
return [
	'tgBotToken' => 'BOT TOKEN',
	'groupID' => 'YOUR GROUP ID'
];
```

Bot can
 - Delete join user messages
 - Delete left user messages  

Todo
  - Create installation script
  - Greetings for new members
  - Setting limit /ban command for any message and count /ban for messages, after over the limit ban author of message
  - ...

You can suggest extra function.
----
For contact  
Telegram https://t.me/ErkinPardayev  
Email: erkin.pardayev@gmail.com
or create issue [here](https://github.com/Linuxiston/tg-admin-helper/issues)
