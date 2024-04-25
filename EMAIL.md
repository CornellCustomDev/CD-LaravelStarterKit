# Prevent Unwanted Email Sending in Laravel

Sometimes we don't want our Laravel application to send email to customers.  Here are some tricks to control email sending.

## Setting Global Addresses

This technique is available starting in Laravel version 8.0. 

Laravel allows you to set the application [to always send to a specific address](https://laravel.com/docs/9.x/mail#using-a-global-to-address). You may want to set this for any non-production environment.

This is done via `Mail::alwaysTo()`. To use it, add the following to the `app/Providers/AppServiceProvider.php` class:

```
use Illuminate\Support\Facades\Mail; 

// later, within the AppServiceProvider class... 

public function boot() { 

// If we're not production or staging, use a fake address 

	if (! $this->app->environment('production', 'staging')) {
		Mail::alwaysTo('testing@example.org'); 
	} 
}
```

It's _generally_ safe to send test emails to the `@example.org` domain. It actually exists for this purpose. That being said, don't go sending confidential information there.   Of course, you can set the target address to anything you want.  Use a configuration variable connected to an environment variable for this so you set the target address in the .env file. 

Note: there is also an alwaysFrom() function available. 

## Set a No-Op Driver

There's a mail driver you can use that stops Laravel from sending emails. Instead, it will log emails out to your set log channel (the `laravel.log` file by default).

This can be done by setting environment variable `MAIL_MAILER` to `log`.   

The log driver is available starting in Laravel version 8.0.

In your .env file:
```
MAIL_MAILER=log
```


## Use a Service

If you need more advanced features (inspecting the emails being sent, storing them, etc.) there are some services you can use.  For example:  [Mailhog](https://github.com/mailhog/MailHog) can be installed locally (or on some servers) and be used with the `smtp` mail driver.  

There is a [Mailhog](https://docs.lando.dev/mailhog/) plugin in Lando for local development.  

You can  add Mailhog to you Lando app by adding an entry. to the `services` top level config in your Landofile.

```
  services:
    mail:
      type: mailhog
      hogfrom:
        - appserver
```

See the Lando documentation for more details.

To be investigated:  installing Mailhog at Media3. 



