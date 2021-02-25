<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Woosa\Adyen\Monolog\Test;

use Woosa\Adyen\Monolog\Logger;
use Woosa\Adyen\Monolog\DateTimeImmutable;
use Woosa\Adyen\Monolog\Formatter\FormatterInterface;
/**
 * Lets you easily generate log records and a dummy formatter for testing purposes
 * *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class TestCase extends \Woosa\Adyen\PHPUnit\Framework\TestCase
{
    /**
     * @return array Record
     */
    protected function getRecord($level = \Woosa\Adyen\Monolog\Logger::WARNING, $message = 'test', array $context = []) : array
    {
        return ['message' => (string) $message, 'context' => $context, 'level' => $level, 'level_name' => \Woosa\Adyen\Monolog\Logger::getLevelName($level), 'channel' => 'test', 'datetime' => new \Woosa\Adyen\Monolog\DateTimeImmutable(\true), 'extra' => []];
    }
    protected function getMultipleRecords() : array
    {
        return [$this->getRecord(\Woosa\Adyen\Monolog\Logger::DEBUG, 'debug message 1'), $this->getRecord(\Woosa\Adyen\Monolog\Logger::DEBUG, 'debug message 2'), $this->getRecord(\Woosa\Adyen\Monolog\Logger::INFO, 'information'), $this->getRecord(\Woosa\Adyen\Monolog\Logger::WARNING, 'warning'), $this->getRecord(\Woosa\Adyen\Monolog\Logger::ERROR, 'error')];
    }
    /**
     * @suppress PhanTypeMismatchReturn
     */
    protected function getIdentityFormatter() : \Woosa\Adyen\Monolog\Formatter\FormatterInterface
    {
        $formatter = $this->createMock(\Woosa\Adyen\Monolog\Formatter\FormatterInterface::class);
        $formatter->expects($this->any())->method('format')->will($this->returnCallback(function ($record) {
            return $record['message'];
        }));
        return $formatter;
    }
}
