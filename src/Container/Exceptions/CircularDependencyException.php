<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;

final class CircularDependencyException extends Exception
{
    public function __construct(ContainerLog $containerLog)
    {
        $stack = $containerLog->getStack();

        $firstContext = $stack[array_key_first($stack)];
        $lastContext = $stack[array_key_last($stack)];

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstContext->getId()} because it is a circular dependency:" . PHP_EOL;

        $i = 0;

        foreach ($stack as $currentContext) {
            $pipe = match ($i) {
                0 => '┌─►',
                default => '│  ',
            };

            $message .= PHP_EOL . "\t{$pipe} " . $currentContext;

            $i++;
        }


        $circularName = explode('::', (string) $firstContext)[0] ?? null;
        $firstPart = explode($circularName, (string) $lastContext)[0] ?? null;
        $fillerLines = str_repeat('─', strlen($firstPart) + 3);
        $fillerArrows = str_repeat('▒', strlen($circularName));

        $message .= PHP_EOL . "\t└{$fillerLines}{$fillerArrows}";
        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$containerLog->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
