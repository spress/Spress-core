<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\Tests\ContentManager\Converter;

use PHPUnit\Framework\TestCase;
use Yosymfony\Spress\Core\ContentManager\Converter\ConverterResult;

class ConverterResultTest extends TestCase
{
    public function testConvertertResult()
    {
        $result = new ConverterResult('My text', 'md', 'html');

        $this->assertEquals('My text', $result->getResult());
        $this->assertEquals('md', $result->getInputExtension());
        $this->assertEquals('html', $result->getExtension());
    }
}
