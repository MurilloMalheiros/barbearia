<?php

declare(strict_types=1);

namespace App\models;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

final class Logger
{
    private static ?MonologLogger $instance = null;

    private static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            $logger  = new MonologLogger('barbearia');
            $handler = new RotatingFileHandler(
                LOG_PATH . '/app.log',
                30,
                MonologLogger::ERROR
            );
            $handler->setFormatter(new LineFormatter(null, null, true, true));
            $logger->pushHandler($handler);
            self::$instance = $logger;
        }
        return self::$instance;
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }
}
