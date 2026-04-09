<?php

namespace App\Http\Controllers\Backend\Configurations;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
        Gate::authorize('config:edit-school');

        $config = Configuration::first();

        if (!$config) {
            $config = new Configuration();
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:250'],
            'legal_name' => ['nullable', 'string', 'max:250'],
            'legal_id' => ['nullable', 'string', 'max:250'],
            'country' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:250'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:250'],
            'website' => ['nullable', 'string', 'max:250'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'locale' => ['nullable', 'string', 'max:20'],
            'sport' => ['nullable', 'integer'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'remove_logo' => ['nullable', 'in:0,1'],
        ]);

        $config->fill(collect($validated)->except(['logo_file', 'remove_logo'])->all());

        if (($validated['remove_logo'] ?? '0') === '1') {
            if (!empty($config->logo) && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }
            $config->logo = null;
        }

        if ($request->hasFile('logo_file')) {
            if (!empty($config->logo) && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }

            $config->logo = $request->file('logo_file')->store('configurations/logo', 'public');
        }

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
