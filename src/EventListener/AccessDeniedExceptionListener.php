<?php

namespace App\EventListener;

use App\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AccessDeniedExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
        }
    }
}