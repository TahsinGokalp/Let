<?php

namespace Let\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use TahsinGokalp\Let;
use Throwable;

class LetHandler extends AbstractProcessingHandler
{
    protected $let;

    /**
     * @param  int  $level
     */
    public function __construct(Let $let, $level = Logger::ERROR, bool $bubble = true)
    {
        $this->let = $let;

        parent::__construct($level, $bubble);
    }

    /**
     * @param  array  $record
     */
    protected function write($record): void
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->let->handle(
                $record['context']['exception']
            );

            return;
        }
    }
}
