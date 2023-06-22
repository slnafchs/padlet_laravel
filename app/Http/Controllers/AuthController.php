<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //definiert den Konstruktor einer Klasse und weist dem Middleware 'auth:api' zu
    public function __construct() {
        $this->middleware('auth:api', ['except' =>  ['login']]);
    }

    //login
    //nimmt die E-Mail-Adresse und das Passwort aus der Anfrage entgegen und überprüft, ob die bereitgestellten
    //Anmeldeinformationen korrekt sind
    public function login() {
        $credentials = request(['email', 'password']);
        //wenn Anmeldeinformationen nicht korrekt sind, dann Error
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //wenn Anmeldedaten korrekt sind, wird ein JWT Token generiert
        return $this->respondWithToken($token);
    }

    //gibt eine JSON Antwort zurück, die das Zugriffstoken, den Tokentyp und die Ablaufzeit des Tokens enthält
    protected function respondWithToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer', //Zugriffstoken wird als Bearer-Token verwendet
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    //gibt die Informationen des authentifizierten Benutzers als JSON-Antwort zurück
    public function me() {
        //auth()->user()-Funktion, um den aktuellen authentifizierten Benutzer abzurufen
        return response()->json(auth()->user());
    }

    //Logout-Vorgang des authentifizierten Benutzers
    //Benutzer wird aus der aktuellen Sitzung abgemeldet
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    //Aktualisierung des Zugriffstokens des authentifizierten Benutzers
    public function refresh() {
        //neues gültiges Zugriffstoken generieren
        return $this->respondWithToken(auth()->refresh());
    }
}

