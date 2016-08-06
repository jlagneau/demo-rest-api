<?php
/**
 * interface in order to mock forminteraface waiting https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103.
 */
namespace  tests\BlogBundle;

use Symfony\Component\Form\FormInterface as FI;

interface FormInterface extends \Iterator, FI
{
}
