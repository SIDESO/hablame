# Hablame SMS Notifications Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sideso/hablame.svg?style=flat-square)](https://packagist.org/packages/sideso/hablame)
[![Total Downloads](https://img.shields.io/packagist/dt/sideso/hablame.svg?style=flat-square)](https://packagist.org/packages/sideso/hablame)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package makes it easy to send notifications using [Hablame](https://www.hablame.co) with Laravel 6.x, 7.x, 8.x and 9.x

## Contents

- [Hablame SMS Notifications Channel for Laravel](#hablame-sms-notifications-channel-for-laravel)
	- [Contents](#contents)
	- [Installation](#installation)
		- [Setting up the hablame service](#setting-up-the-hablame-service)
	- [Usage](#usage)
		- [On-Demand Notifications](#on-demand-notifications)
		- [Available Message methods](#available-message-methods)
		- [Available Events](#available-events)
	- [Changelog](#changelog)
	- [Testing](#testing)
	- [Security](#security)
	- [Credits](#credits)
	- [License](#license)


## Installation

You can install this package via composer:
``` bash
composer require sideso/hablame
```

### Setting up the hablame service

You may publish the config file and add your hablame token, api key and account number to your config/hablame.php:

```bash
php artisan vendor:publish --provider="Sideso\Hablame\HablameServiceProvider" --tag="config"
```

```php
// config/hablame.php
...
'hablame' => [
	'account' => env('HABLAME_ACCOUNT',''),
	'api_key' => env('HABLAME_API_KEY',''),
	'token' => env('HABLAME_TOKEN'.''),
	'source_code' => env('HABLAME_SOURCE_CODE',''),
],
...
```

## Usage

You can use the channel in your via() method inside the notification:

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\hablame\HablameMessage;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return ["hablame"];
    }

    public function tohablame($notifiable)
    {
        return (new HablameMessage)->content("Your account was approved!");       
    }
}
```

In your notifiable model, make sure to include a routeNotificationForhablame() or routeNotificationForSMS()  method, which returns a phone number.

```php
public function routeNotificationForhablame()
{
    return $this->phone;
}
```
### On-Demand Notifications
Sometimes you may need to send a notification to someone who is not stored as a "user" of your application. Using the Notification::route method, you may specify ad-hoc notification routing information before sending the notification:

```php
Notification::route('hablame', '573001234567')                      
            ->notify(new InvoicePaid($invoice));
```
### Available Message methods

`content()`: Set a content of the notification message. This parameter should be no longer than 918 char(6 message parts).

`sourceCode()`: Set the Source Code name to be used as origin.

`requestProofOfDelivery()`: Set the request proof of delivery to be used as origin (Extra cost).

`priority()`: Set the priority of the message. (True = Transactional, False = Marketing).

`flash()`: Set the if the message should be flash. (True = Flash, False = Normal).

`senDate()`: Set the date and time when the message should be sent. (Not used is priority is set to true).

`withCallback()`: Set the callback function to be executed when the message is sent.

`tags()`: Set the tags to be used to send the message.

### Available Events

`Sideso\SMS\SMSSent`: This event is fired when the message is sent.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email ctrujillo@sideso.com.co instead of using the issue tracker.

## Credits

- [Carlos Trujillo](https://github.com/IGedeon)
- [SIDESO](https://github.com/SIDESO)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
