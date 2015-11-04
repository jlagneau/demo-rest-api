# Demo REST API

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f/mini.png)](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f)
[![Build Status](https://travis-ci.org/jlagneau/demo-rest-api.svg)](https://travis-ci.org/jlagneau/demo-rest-api)
[![Coverage Status](https://img.shields.io/coveralls/jlagneau/demo-rest-api.svg)](https://coveralls.io/r/jlagneau/demo-rest-api)

---

## Install and run

Inside the directory:

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

Change configurations in `app/config/parameters.yml` if needed and run :

	$ php app/console doctrine:database:create
	$ php app/console doctrine:schema:create

For development purpose, you can also load fixtures :

	$ phpapp/console doctrine:fixtures:load

Now you can start the server (for development only) :

    $ php app/console server:run &

## API doc

see [http://localhost:8000/app_dev.php/doc/](http://localhost:8000/app_dev.php/doc/).

## Tests

    $ phpunit -c app/
