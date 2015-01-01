# API for soyel.fr

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f/mini.png)](https://insight.sensiolabs.com/projects/51a77b1b-c6bd-4dde-8b7f-563a7ab3036f)
[![Build Status](https://travis-ci.org/soyel/api.soyel.fr.svg?branch=master)](https://travis-ci.org/soyel/api.soyel.fr)
[![Coverage Status](https://img.shields.io/coveralls/soyel/api.soyel.fr.svg)](https://coveralls.io/r/soyel/api.soyel.fr)

---

## Install and run

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

Change configurations in `app/config/parameters.yml` if needed and run :

    $ php app/console server:run &

## API doc

see http://localhost/doc/.

## Tests

    $ phpunit -c app/
