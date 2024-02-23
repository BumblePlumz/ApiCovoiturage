<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\NonUniqueFieldNameException;
use App\Utils\ValidationException;
use App\Utils\NotFoundException;


final class ExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        switch (true) {
                // -----------------------
                // DOCTRINE EXCEPTIONS
                // -----------------------
            case $exception instanceof UniqueConstraintViolationException:
                $responseData = [
                    'success' => false,
                    'message' => 'Une des données que vous avez entrées existe déjà!',
                    'status_code' => Response::HTTP_CONFLICT
                ];
                break;
            case $exception instanceof ConnectionException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la connexion à la base de données!',
                    'status_code' => Response::HTTP_SERVICE_UNAVAILABLE
                ];
                break;
            case $exception instanceof InvalidArgumentException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des données!',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ];
                break;
            case $exception instanceof ForeignKeyConstraintViolationException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression de la ressource!',
                    'status_code' => Response::HTTP_CONFLICT
                ];
                break;
            case $exception instanceof NotNullConstraintViolationException:
                $responseData = [
                    'success' => false,
                    'message' => 'Une des données que vous avez entrées est invalide!',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ];
                break;
            case $exception instanceof ConstraintViolationException:
                $responseData = [
                    'success' => false,
                    'message' => 'Une des données que vous avez entrées est invalide!',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ];
                break;
            case $exception instanceof TableNotFoundException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des données!',
                    'status_code' => Response::HTTP_NOT_FOUND
                ];
                break;
            case $exception instanceof NonUniqueFieldNameException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des données!',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ];
                break;
            case $exception instanceof PDOException:
                $responseData = [
                    'success' => false,
                    'message' => 'Erreur lors de la connexion à la base de donnée!',
                    'status_code' => Response::HTTP_SERVICE_UNAVAILABLE
                ];
                // -----------------------
                // CUSTOM EXCEPTION
                // -----------------------
            case $exception instanceof ValidationException:
                $responseData = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'status_code' => $exception->getCode()
                ];
                break;
            case $exception instanceof NotFoundException:
                $responseData = [
                    'success' => false,
                    'message' => $exception->getCode()
                ];
                break;

                // -----------------------
                // HTTP EXCEPTION
                // -----------------------
            case $exception instanceof HttpException:
                $responseData = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'status_code' => $exception->getCode()
                ];
                break;

                // -----------------------
                // DEFAULT EXCEPTION
                // -----------------------
            default:
                $responseData = [
                    'success' => false,
                    'message' => 'Une erreur inopinée est survenue, merci de contacter un administrateur!',
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR
                ];
        }

        $response = new JsonResponse($responseData, $responseData['status_code']);
        $event->setResponse($response);
    }
}