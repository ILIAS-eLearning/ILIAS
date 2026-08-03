<?php

declare(strict_types=1);

final class ilLTIConsumerHttpException extends RuntimeException
{
    public static function badRequest(string $message): self
    {
        return new self($message, 400);
    }

    public static function unauthorized(string $message = 'invalid request'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message): self
    {
        return new self($message, 403);
    }

    public static function notFound(string $message): self
    {
        return new self($message, 404);
    }
}
