<?php

namespace SimpleBus\Message\Bus\Middleware;

use Exception;

class FinishesHandlingMessageBeforeHandlingNext implements MessageBusMiddleware
{
    /**
     * @var array
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $isHandling = false;

    /**
     * Completely finishes handling the current message, before allowing other middlewares to start handling new
     * messages.
     *
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $this->queue[] = $message;

        if (!$this->isHandling) {
            $this->isHandling = true;

            while ($message = array_shift($this->queue)) {
                try {
                    $next($message);
                } catch (Exception $exception) {
                    $this->isHandling = false;

                    throw $exception;
                }
            }

            $this->isHandling = false;
        }
    }
}
