<?php

namespace App;

use GuzzleHttp\Psr7\Request;

trait ApiHelperTrait
{
    /**
     * Creates a Psr7 Request for the *mysterious* API, from a resource endpoint.
     */
    public static function getRequest(string $endpoint): Request
    {
        return new Request('GET', "https://b-viguier.github.io/Afup-Workshop-Async/api$endpoint");
    }
}