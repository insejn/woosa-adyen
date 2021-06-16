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

use Woosa\Adyen\Monolog\ResettableInterface;
use Woosa\Adyen\Monolog\Formatter\FormatterInterface;
/**
 * This simple wrapper class can be used to extend handlers functionality.
 *
 * Example: A custom filtering that can be applied to any handler.
 *
 * Inherit from this class and override handle() like this:
 *
 *   public function handle(array $record)
 *   {
 *        if ($record meets certain conditions) {
 *            return false;
 *        }
 *        return $this->handler->handle($record);
 *   }
 *
 * @author Alexey Karapetov <alexey@karapetov.com>
 */
class HandlerWrapper implements \Woosa\Adyen\Monolog\Handler\HandlerInterface, \Woosa\Adyen\Monolog\Handler\ProcessableHandlerInterface, \Woosa\Adyen\Monolog\Handler\FormattableHandlerInterface, \Woosa\Adyen\Monolog\ResettableInterface
{
    /**
     * @var HandlerInterface
     */
    protected $handler;
    public function __construct(\Woosa\Adyen\Monolog\Handler\HandlerInterface $handler)
    {
        $this->handler = $handler;
    }
    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record) : bool
    {
        return $this->handler->isHandling($record);
    }
    /**
     * {@inheritdoc}
     */
    public function handle(array $record) : bool
    {
        return $this->handler->handle($record);
    }
    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records) : void
    {
        $this->handler->handleBatch($records);
    }
    /**
     * {@inheritdoc}
     */
    public function close() : void
    {
        $this->handler->close();
    }
    /**
     * {@inheritdoc}
     */
    public function pushProcessor(callable $callback) : \Woosa\Adyen\Monolog\Handler\HandlerInterface
    {
        if ($this->handler instanceof \Woosa\Adyen\Monolog\Handler\ProcessableHandlerInterface) {
            $this->handler->pushProcessor($callback);
            return $this;
        }
        throw new \LogicException('The wrapped handler does not implement ' . \Woosa\Adyen\Monolog\Handler\ProcessableHandlerInterface::class);
    }
    /**
     * {@inheritdoc}
     */
    public function popProcessor() : callable
    {
        if ($this->handler instanceof \Woosa\Adyen\Monolog\Handler\ProcessableHandlerInterface) {
            return $this->handler->popProcessor();
        }
        throw new \LogicException('The wrapped handler does not implement ' . \Woosa\Adyen\Monolog\Handler\ProcessableHandlerInterface::class);
    }
    /**
     * {@inheritdoc}
     */
    public function setFormatter(\Woosa\Adyen\Monolog\Formatter\FormatterInterface $formatter) : \Woosa\Adyen\Monolog\Handler\HandlerInterface
    {
        if ($this->handler instanceof \Woosa\Adyen\Monolog\Handler\FormattableHandlerInterface) {
            $this->handler->setFormatter($formatter);
            return $this;
        }
        throw new \LogicException('The wrapped handler does not implement ' . \Woosa\Adyen\Monolog\Handler\FormattableHandlerInterface::class);
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter() : \Woosa\Adyen\Monolog\Formatter\FormatterInterface
    {
        if ($this->handler instanceof \Woosa\Adyen\Monolog\Handler\FormattableHandlerInterface) {
            return $this->handler->getFormatter();
        }
        throw new \LogicException('The wrapped handler does not implement ' . \Woosa\Adyen\Monolog\Handler\FormattableHandlerInterface::class);
    }
    public function reset()
    {
        if ($this->handler instanceof \Woosa\Adyen\Monolog\ResettableInterface) {
            $this->handler->reset();
        }
    }
}
