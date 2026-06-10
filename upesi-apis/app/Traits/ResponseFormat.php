<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response as FunctionType;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

trait ResponseFormat
{
    /**
     * Pour les créations réussies (POST)
     */
    public function ResponseCreated(string $message, $data = null): JsonResponse
    {
        return FunctionType::json([
            "status" => true,
            "message" => $message,
            "data" => $data
        ], HttpFoundationResponse::HTTP_CREATED);
    }

    /**
     * Pour les lectures ou mises à jour réussies (GET, PUT, PATCH)
     */
    public function ResponseOk(string $message, $data = null, $pagination = null): JsonResponse
    {
        return FunctionType::json([
            "status" => true,
            "message" => $message,
            "meta" => $pagination,
            "data" => $data
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function ResponseUnauthorize(string $message = "Non autorisé"): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
        ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
    }

    public function ResponseForbidden(string $message = "Action interdite"): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
        ], HttpFoundationResponse::HTTP_FORBIDDEN);
    }

    public function ResponseERROR(string $message, $reason = null): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
            "reason" => $reason,
        ], HttpFoundationResponse::HTTP_BAD_REQUEST);
    }

    public function ResponseNotFound(string $message = "Ressource introuvable"): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * CRUCIAL pour Laravel Validation
     */
    public function ResponseUnprocessableEntity(string $message, $errors = null): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
            "errors" => $errors, // C'est ici que Laravel met les erreurs par champ
        ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function ResponseServerError(string $message = "Erreur interne du serveur", $reason = null): JsonResponse
    {
        return FunctionType::json([
            "status" => false,
            "message" => $message,
            "reason" => config('app.debug') ? $reason : "Contactez le support", // Sécurité : on ne montre l'erreur qu'en debug
        ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
