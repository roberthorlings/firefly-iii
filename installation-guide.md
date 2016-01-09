---
layout: page
title: Installation guide
permalink: /installation-guide/
---

# Normal installation

To install Firefly III you'll need to have the following:

* A web server (Apache, nginx);
* PHP 5.6+
* [PHP BCMath Arbitrary Precision Mathematics](http://php.net/manual/en/book.bc.php), a PHP module.
* [PHP Mcrypt](http://php.net/manual/en/book.mcrypt.php)
* [PHP MBstring](http://php.net/manual/en/book.mbstring.php)
* A GMail address or other mailing facilities.
* Access to [Composer](https://getcomposer.org/)
* Make sure you have a MySQL database ready, together with the username and password for that database.
* Firefly III has a lot of dependencies. It helps if you have a Github account.

Because Firefly III needs some special tools to install it, you must have access to the terminal on your web server. It's simply not enough to have web hosting where you may upload stuff. In theory however, it is possible to do all of this locally and then upload it to your web server.

## Installation steps

Login to your web server and go to the directory where you want to install Firefly III. Please keep in mind that the web root of Firefly III is in the ``firefly-iii/public/`` directory, so you may need to update your web server configuration.

Once you're there, run the following command:

* ``git clone https://github.com/JC5/firefly-iii.git . --depth 1``

Or variants:

* ``git clone https://github.com/JC5/firefly-iii.git some-other-dir --depth 1``
* ``git clone https://github.com/JC5/firefly-iii.git --depth 1`` (defaults to ``firefly-iii``)

***

Then, run this command:

``copy .env.example .env``

Open ``firefly-iii/.env``.

* Change the ``DB_*`` settings as you see fit.
* Update the ``MAIL_*`` settings as you see fit.
* If you want to track statistics, update the Google Analytics ID.
* Set ``RUNCLEANUP`` to ``false``
* Set ``SITE_OWNER`` to your own email address.

Once you've set this up, run the following commands:

* ``cd firefly-iii`` (or how you've named your folder)
* ``composer install``
* ``php artisan migrate --seed --env=production``

Finally, make sure that the storage directories are writeable, _for example_ by using these commands:

* ``chown -R www-data:www-data storage``
* ``chmod -R g+w storage``

### Registering

Surf to your web server, the ``public/`` directory is your root. You may want to change your web server's configuration so you can surf to ``/`` and get Firefly.

You will see a Sign In screen. Use the Register pages to create a new account. After you've created a new account, you will get an introduction screen.

## Installation errors

Some common errors:

### 500 errors, logs are empty

If the logs are empty (``storage/logs``) Firefly can't write to them. See above for the commands. If the logs still remain empty, do you have a the ``vendor`` in your Firefly root? If not, run the Composer commands.

### Return value

`Fatal error: Can't use method return value in write context in /var/www/firefly-iii/bootstrap/cache/compiled.php on line 646`

Solution: Use PHP 5.6 or higher, not 5.4

### BCMath

`PHP message: PHP Fatal error: Call to undefined function FireflyIII\Http\Controllers\bcscale() in /var/www/firefly-iii/app/Http/Controllers/HomeController.php on line 76`

Solution: you haven't enabled or installed the BCMath module.