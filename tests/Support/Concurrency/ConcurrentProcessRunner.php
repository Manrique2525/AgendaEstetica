<?php

declare(strict_types=1);

namespace Tests\Support\Concurrency;

use RuntimeException;

final class ConcurrentProcessRunner
{
    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    public function start(array $payloads): ConcurrentProcessRun
    {
        return new ConcurrentProcessRun($payloads);
    }
}

final class ConcurrentProcessRun
{
    /**
     * @var array<int, array{process: resource, pipes: array<int, resource>, ready: string, result: string}>
     */
    private array $workers = [];

    private string $directory;

    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    public function __construct(array $payloads)
    {
        $this->directory = sys_get_temp_dir().'/spec004-concurrency-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700, true);

        foreach ($payloads as $index => $payload) {
            $ready = "{$this->directory}/ready-{$index}";
            $result = "{$this->directory}/result-{$index}.json";
            $command = [
                PHP_BINARY,
                dirname(__DIR__, 3).'/tests/Support/Concurrency/worker.php',
                json_encode($payload, JSON_THROW_ON_ERROR),
                $ready,
                $result,
                "{$this->directory}/start",
            ];
            $pipes = [];
            $process = proc_open($command, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes, dirname(__DIR__, 3), [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => (string) config('database.connections.mysql.host'),
                'DB_PORT' => (string) config('database.connections.mysql.port'),
                'DB_DATABASE' => (string) config('database.connections.mysql.database'),
                'DB_USERNAME' => (string) config('database.connections.mysql.username'),
                'DB_PASSWORD' => (string) config('database.connections.mysql.password'),
            ]);

            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start concurrency worker.');
            }

            $this->workers[$index] = compact('process', 'pipes', 'ready', 'result');
        }
    }

    public function awaitReady(int $timeoutMilliseconds = 10000): void
    {
        $deadline = microtime(true) + ($timeoutMilliseconds / 1000);

        while (microtime(true) < $deadline) {
            if (count(array_filter($this->workers, static fn (array $worker): bool => file_exists($worker['ready']))) === count($this->workers)) {
                return;
            }

            usleep(10000);
        }

        $this->terminate();
        throw new RuntimeException('Concurrency workers did not become ready in time.');
    }

    public function release(): void
    {
        touch("{$this->directory}/start");
    }

    public function isComplete(): bool
    {
        return count(array_filter($this->workers, static fn (array $worker): bool => file_exists($worker['result']))) === count($this->workers);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function wait(int $timeoutMilliseconds = 15000): array
    {
        $deadline = microtime(true) + ($timeoutMilliseconds / 1000);

        while (! $this->isComplete() && microtime(true) < $deadline) {
            usleep(10000);
        }

        if (! $this->isComplete()) {
            $this->terminate();
            throw new RuntimeException('Concurrency worker timed out.');
        }

        $results = [];

        foreach ($this->workers as $worker) {
            $results[] = json_decode((string) file_get_contents($worker['result']), true, 512, JSON_THROW_ON_ERROR);
            foreach ($worker['pipes'] as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            proc_close($worker['process']);
            unlink($worker['ready']);
            unlink($worker['result']);
        }

        unlink("{$this->directory}/start");
        rmdir($this->directory);

        return $results;
    }

    private function terminate(): void
    {
        foreach ($this->workers as $worker) {
            if (is_resource($worker['process'])) {
                proc_terminate($worker['process']);
                proc_close($worker['process']);
            }
            foreach ($worker['pipes'] as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            foreach ([$worker['ready'], $worker['result']] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        if (file_exists("{$this->directory}/start")) {
            unlink("{$this->directory}/start");
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }
}
