<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class RequestValidationManager
{
    public function validateRequest(array $requiredParameters, array $requestData): array|bool
    {

        foreach ($requiredParameters as  $value) {
            if (!array_key_exists($value, $requestData)) {
                $missedParameters[] = $value;
            }
        }
        if (isset($missedParameters)) {
            return ['errors' => 'Missing required parameters - ' . implode(',', $missedParameters)];
        }
        return true;
    }
}