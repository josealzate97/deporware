<?php

namespace App\Http\Controllers\Backend\Configurations;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function index()
    {
        $config = Configuration::first();

        if (request()->expectsJson()) {
            return response()->json([
                'config' => $config,
            ]);
        }

        return view('backend.configurations.general.index', [
            'config' => $config,
            'sports' => Configuration::sportOptions(),
            'countries' => Configuration::countryOptions(),
            'currencies' => Configuration::currencyOptions(),
            'timezones' => Configuration::timezoneOptions(),
            'locales' => Configuration::localeOptions(),
        ]);
    }

    public function update(Request $request)
    {
        $config = Configuration::first();

        if (!$config) {
            $config = new Configuration();
        }

        $config->fill($request->only($config->getFillable()));
        $config->save();

        session()->put('config_country', $config->country);
        session()->put('config_currency', $config->currency);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Configuracion actualizada.',
                'config' => $config,
            ]);
        }

        return redirect()->route('configurations.index');
    }
}
