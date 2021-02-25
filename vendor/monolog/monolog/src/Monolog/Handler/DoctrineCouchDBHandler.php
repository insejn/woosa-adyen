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
namespace Woosa\Adyen\Monolog\Handler;

use Woosa\Adyen\Monolog\Logger;
use Woosa\Adyen\Monolog\Formatter\NormalizerFormatter;
use Woosa\Adyen\Monolog\Formatter\FormatterInterface;
use Woosa\Adyen\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \Woosa\Adyen\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\Woosa\Adyen\Doctrine\CouchDB\CouchDBClient $client, $level = \Woosa\Adyen\Monolog\Logger::DEBUG, bool $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record) : void
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter() : \Woosa\Adyen\Monolog\Formatter\FormatterInterface
    {
        return new \Woosa\Adyen\Monolog\Formatter\NormalizerFormatter();
    }
}
