<?php

namespace Woosa\Adyen\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\Woosa\Adyen\Psr\Log\LoggerInterface $logger);
}
