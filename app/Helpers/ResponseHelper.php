<?php

if (! function_exists('formatResponse')) {
    /**
     * Format standart response
     * @param string|array $message.
     */
    function formatResponse($message, int $code, bool $status): array
    {
        return [
            'content' => [
                'message' => $message,
                'success' => $status,
            ],
            'code' => $code
        ];
    }

}
