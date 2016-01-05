# Demo REST API

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f/mini.png)](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f)
[![Build Status](https://travis-ci.org/jlagneau/demo-rest-api.svg)](https://travis-ci.org/jlagneau/demo-rest-api)
[![Coverage Status](https://img.shields.io/coveralls/jlagneau/demo-rest-api.svg)](https://coveralls.io/r/jlagneau/demo-rest-api)

---

This is a simple symfony2 demo of a blog in REST

## Install and run

Inside the directory:

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

Change configurations in `app/config/parameters.yml` if needed and run :

	$ php bin/console doctrine:database:create
	$ php bin/console doctrine:schema:create

Now you can start the server (for development only) :

    $ php bin/console server:start

## API doc

see [http://localhost:8000/app_dev.php/doc](http://localhost:8000/app_dev.php/doc) once the webserver is started.

## Tests

    $ php bin/phpunit
