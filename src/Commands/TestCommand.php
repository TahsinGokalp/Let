<?php

namespace TahsinGokalp\Lett\Commands;

use Exception;
use Illuminate\Console\Command;
use RuntimeException;
use TahsinGokalp\Lett\Events\FailedToSentExceptionToLett;
use TahsinGokalp\Lett\Events\SentExceptionToLett;
use TahsinGokalp\Lett\Lett;
use TahsinGokalp\LettConstants\Enum\ApiResponseCodeEnum;
use Throwable;

class TestCommand extends Command
{
    public $signature = 'lett:test';

    public $description = 'Generate a test exception and send it to lett';

    public function handle(): int
    {
        try {
            /* @var Lett $lett*/
            $lett = app('lett');

            $response = $lett->handle(
                $this->generateException()
            );

            if (is_null($response)) {
                $this->info(trans('lett::lett.sent_exception_to_lett'));
                event(new SentExceptionToLett);
            } elseif (! is_bool($response)) {
                $response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                if ((int) $response['code'] === ApiResponseCodeEnum::Success->value) {
                    $this->info(trans('lett::lett.sent_exception_to_lett'));
                    event(new SentExceptionToLett);
                } else {
                    $this->error(trans('lett::lett.failed_to_send_exception_to_lett'));
                    event(new FailedToSentExceptionToLett);
                }
            } else {
                $this->error(trans('lett::lett.failed_to_send_exception_to_lett'));
                event(new FailedToSentExceptionToLett);
            }
        } catch (Exception $ex) {
            $this->error(trans('lett::lett.failed_to_send_exception_to_lett') . " {$ex->getMessage()}");
            event(new FailedToSentExceptionToLett);
        }

        return self::SUCCESS;
    }

    public function generateException(): Throwable
    {
        try {
            throw new RuntimeException(trans('lett::lett.this_is_a_test_exception_from_the_lett_console'));
        } catch (RuntimeException $ex) {
            return $ex;
        }
    }
}
