<?php
/**
 * interface in order to mock forminteraface waiting https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
 */
namespace  BlogBundle\Tests;

use Symfony\Component\Form\FormInterface as FI;

interface FormInterface extends \Iterator, FI
{
}
