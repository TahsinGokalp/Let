<?php

namespace LettLogger;

use Lett\Lett;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Throwable;

class LettHandler extends AbstractProcessingHandler
{
    protected Lett $lett;

    /**
     * @param int $level
     */
    public function __construct(Lett $lett, $level = Level::Error, bool $bubble = true)
    {
        $this->lett = $lett;

        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write($record): void
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->lett->handle(
                $record['context']['exception']
            );
        }
    }
}
