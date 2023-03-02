<?php

namespace LettLogger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use TahsinGokalp\Lett;
use Throwable;

class LettHandler extends AbstractProcessingHandler
{
    protected $lett;

    /**
     * @param  int  $level
     */
    public function __construct(Lett $lett, $level = Logger::ERROR, bool $bubble = true)
    {
        $this->lett = $lett;

        parent::__construct($level, $bubble);
    }

    /**
     * @param  array  $record
     */
    protected function write($record): void
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->lett->handle(
                $record['context']['exception']
            );

            return;
        }
    }
}
