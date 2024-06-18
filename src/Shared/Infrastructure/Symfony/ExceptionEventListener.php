<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony;

use App\Shared\Infrastructure\Exceptions\ConcurrencyException;
use App\Shared\Infrastructure\Exceptions\RecordNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ExceptionEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $response = $this->getResponse($event->getThrowable());

        $event->setResponse($response);
        $response->headers->set('Content-Type', 'application/json');
    }

    private function getResponse(Throwable $t): JsonResponse
    {
        return match (true) {
            $t instanceof ConcurrencyException => new JsonResponse(null, Response::HTTP_CONFLICT),
            $t instanceof RecordNotFoundException => new JsonResponse(null, Response::HTTP_NOT_FOUND),
            default => new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }
}
