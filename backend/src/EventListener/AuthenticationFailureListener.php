<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AuthenticationFailureListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof CustomUserMessageAccountStatusException) {
            $response = new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 401,
            ], 401);

            $event->setResponse($response);
        }
    }
}
