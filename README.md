GCM-via-PHP
===========

Send GCM Messages via PHP and handle the Result-Codes. Super lightweight!

Module Requirements:
----
* php5-curl

Usage Example
----
Just add it to your composer-file!

Example composer.json file here:
```json
{
    "repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Jan1337z/GCM-via-PHP"
    }
    ],
    "require": {
        "GCMSender": "dev-master"
    }
}

```


possible index.php
```php
<?php
    require 'vendor/autoload.php';
    $sender = new GCM\Sender("YOUR_API_KEY");
    $sender->setRecipients(array("a_android_client_registration_id"));
    $sender->sendMessage(array("message" => "some_content"));
?>
```

more following!
