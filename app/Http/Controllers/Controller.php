<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

//erbt sie die Funktionalitäten dieser Traits und ermöglicht es, Autorisierungsanfragen zu verarbeiten,
//DispatchesJobs zu versenden und Anfragen zu validieren.
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
