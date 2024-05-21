<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ErrorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -128],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof JsonException) {
            $response = new JsonResponse([
                'message' => 'json payload required',
            ], Response::HTTP_BAD_REQUEST);
        } else {
            $response = new JsonResponse([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);

        error_log($event->getThrowable());
    }
}