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
namespace Woosa\Adyen\Monolog\Formatter;

use Woosa\Adyen\Monolog\Logger;
use Woosa\Adyen\Gelf\Message;
use Woosa\Adyen\Monolog\Utils;
/**
 * Serializes a log message to GELF
 * @see http://docs.graylog.org/en/latest/pages/gelf.html
 *
 * @author Matt Lehner <mlehner@gmail.com>
 */
class GelfMessageFormatter extends \Woosa\Adyen\Monolog\Formatter\NormalizerFormatter
{
    protected const DEFAULT_MAX_LENGTH = 32766;
    /**
     * @var string the name of the system for the Gelf log message
     */
    protected $systemName;
    /**
     * @var string a prefix for 'extra' fields from the Monolog record (optional)
     */
    protected $extraPrefix;
    /**
     * @var string a prefix for 'context' fields from the Monolog record (optional)
     */
    protected $contextPrefix;
    /**
     * @var int max length per field
     */
    protected $maxLength;
    /**
     * Translates Monolog log levels to Graylog2 log priorities.
     */
    private $logLevels = [\Woosa\Adyen\Monolog\Logger::DEBUG => 7, \Woosa\Adyen\Monolog\Logger::INFO => 6, \Woosa\Adyen\Monolog\Logger::NOTICE => 5, \Woosa\Adyen\Monolog\Logger::WARNING => 4, \Woosa\Adyen\Monolog\Logger::ERROR => 3, \Woosa\Adyen\Monolog\Logger::CRITICAL => 2, \Woosa\Adyen\Monolog\Logger::ALERT => 1, \Woosa\Adyen\Monolog\Logger::EMERGENCY => 0];
    public function __construct(?string $systemName = null, ?string $extraPrefix = null, string $contextPrefix = 'ctxt_', ?int $maxLength = null)
    {
        parent::__construct('U.u');
        $this->systemName = \is_null($systemName) || $systemName === '' ? \gethostname() : $systemName;
        $this->extraPrefix = \is_null($extraPrefix) ? '' : $extraPrefix;
        $this->contextPrefix = $contextPrefix;
        $this->maxLength = \is_null($maxLength) ? self::DEFAULT_MAX_LENGTH : $maxLength;
    }
    /**
     * {@inheritdoc}
     */
    public function format(array $record) : \Woosa\Adyen\Gelf\Message
    {
        if (isset($record['context'])) {
            $record['context'] = parent::format($record['context']);
        }
        if (isset($record['extra'])) {
            $record['extra'] = parent::format($record['extra']);
        }
        if (!isset($record['datetime'], $record['message'], $record['level'])) {
            throw new \InvalidArgumentException('The record should at least contain datetime, message and level keys, ' . \var_export($record, \true) . ' given');
        }
        $message = new \Woosa\Adyen\Gelf\Message();
        $message->setTimestamp($record['datetime'])->setShortMessage((string) $record['message'])->setHost($this->systemName)->setLevel($this->logLevels[$record['level']]);
        // message length + system name length + 200 for padding / metadata
        $len = 200 + \strlen((string) $record['message']) + \strlen($this->systemName);
        if ($len > $this->maxLength) {
            $message->setShortMessage(\Woosa\Adyen\Monolog\Utils::substr($record['message'], 0, $this->maxLength));
        }
        if (isset($record['channel'])) {
            $message->setFacility($record['channel']);
        }
        if (isset($record['extra']['line'])) {
            $message->setLine($record['extra']['line']);
            unset($record['extra']['line']);
        }
        if (isset($record['extra']['file'])) {
            $message->setFile($record['extra']['file']);
            unset($record['extra']['file']);
        }
        foreach ($record['extra'] as $key => $val) {
            $val = \is_scalar($val) || null === $val ? $val : $this->toJson($val);
            $len = \strlen($this->extraPrefix . $key . $val);
            if ($len > $this->maxLength) {
                $message->setAdditional($this->extraPrefix . $key, \Woosa\Adyen\Monolog\Utils::substr($val, 0, $this->maxLength));
                continue;
            }
            $message->setAdditional($this->extraPrefix . $key, $val);
        }
        foreach ($record['context'] as $key => $val) {
            $val = \is_scalar($val) || null === $val ? $val : $this->toJson($val);
            $len = \strlen($this->contextPrefix . $key . $val);
            if ($len > $this->maxLength) {
                $message->setAdditional($this->contextPrefix . $key, \Woosa\Adyen\Monolog\Utils::substr($val, 0, $this->maxLength));
                continue;
            }
            $message->setAdditional($this->contextPrefix . $key, $val);
        }
        /** @phpstan-ignore-next-line */
        if (null === $message->getFile() && isset($record['context']['exception']['file'])) {
            if (\preg_match("/^(.+):([0-9]+)\$/", $record['context']['exception']['file'], $matches)) {
                $message->setFile($matches[1]);
                $message->setLine($matches[2]);
            }
        }
        return $message;
    }
}
