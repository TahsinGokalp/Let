<?php

namespace TahsinGokalp\Lett\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use TahsinGokalp\Lett\Lett;
use Throwable;

class LettHandler extends AbstractProcessingHandler
{
    protected Lett $lett;

    public function __construct(Lett $lett, Level $level = Level::Error, bool $bubble = true)
    {
        $this->lett = $lett;

        parent::__construct($level, $bubble);
    }

    protected function write($record): void
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->lett->handle(
                $record['context']['exception']
            );
        }
    }
}
