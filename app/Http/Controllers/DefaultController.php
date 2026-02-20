<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class DefaultController extends Controller {

    /**
     * Mostrar el dashboard con las estadísticas
     */
    public function dashboard() {

        // Retornar vista con los datos
        return view('backend.home');
    }

}
