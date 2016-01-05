# Demo REST API

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f/mini.png)][1]
[![Build Status](https://travis-ci.org/jlagneau/demo-rest-api.svg)][2]
[![Coverage Status](https://img.shields.io/coveralls/jlagneau/demo-rest-api.svg)][3]

---

This is a simple symfony3 demo of a REST API for a blog.

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

see [http://localhost:8000/app_dev.php/][4] once the webserver is started.

## Tests

    $ php bin/phpunit

[1]: https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f
[2]: https://travis-ci.org/jlagneau/demo-rest-api
[3]: https://coveralls.io/r/jlagneau/demo-rest-api
[4]: http://localhost:8000/app_dev.php/
