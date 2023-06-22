<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

//definiert eine Middleware-Klasse namens "Authenticate".
//Die Middleware überprüft, ob der Benutzer authentifiziert ist oder nicht.
//Diese Middleware wird verwendet, um den Zugriff auf geschützte Bereiche einer Anwendung zu
//kontrollieren und sicherzustellen, dass nur authentifizierte Benutzer auf sie zugreifen können.

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        //Wenn der Benutzer nicht authentifiziert ist und die Anfrage nicht im JSON-Format erwartet wird ...
        if (! $request->expectsJson()) {
            // ... wird er zur Login Route weitergeleitet
            return route('login');
        }
    }
}
