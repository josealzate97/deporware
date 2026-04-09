<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Configuration extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'configurations';

    public $incrementing = false;
    protected $keyType = 'string';

    // Deportes
    const SPORT_FOOTBALL = 1;
    const SPORT_BASKETBALL = 2;
    const SPORT_VOLLEYBALL = 3;
    const SPORT_HANDBALL = 4;
    const SPORT_BASEBALL = 5;

    // Paises
    const COUNTRY_CO = 'CO';
    const COUNTRY_ES = 'ES';

    // Monedas
    const CURRENCY_COP = 'COP';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    // Zonas horarias
    const TIMEZONE_BOGOTA = 'America/Bogota';
    const TIMEZONE_MADRID = 'Europe/Madrid';
    const TIMEZONE_NEW_YORK = 'America/New_York';
    const TIMEZONE_BRASIL = 'America/Sao_Paulo';
    const TIMEZONE_BUENOS_AIRES = 'America/Argentina/Buenos_Aires';

    // Locales
    const LOCALE_ES_CO = 'es_CO';
    const LOCALE_ES_ES = 'es_ES';

    protected $fillable = [
        'id',
        'name',
        'legal_name',
        'legal_id',
        'country',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'currency',
        'timezone',
        'locale',
        'sport',
    ];

    protected function casts(): array
    {
        return [
            'sport' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $config): void {
            if (empty($config->id)) {
                $config->id = (string) Str::uuid();
            }
        });
    }

    public static function sportOptions(): array
    {
        return [
            self::SPORT_FOOTBALL => 'Fútbol',
            self::SPORT_BASKETBALL => 'Baloncesto',
            self::SPORT_VOLLEYBALL => 'Voleibol',
            self::SPORT_HANDBALL => 'Balonmano',
            self::SPORT_BASEBALL => 'Béisbol',
        ];
    }

    public static function countryOptions(): array
    {
        return [
            self::COUNTRY_CO => 'Colombia',
            self::COUNTRY_ES => 'España',
        ];
    }

    public static function currencyOptions(): array
    {
        return [
            self::CURRENCY_COP => 'COP',
            self::CURRENCY_EUR => 'EUR',
            self::CURRENCY_USD => 'USD',
        ];
    }

    public static function timezoneOptions(): array
    {
        return [
            self::TIMEZONE_BOGOTA => 'America/Bogota',
            self::TIMEZONE_MADRID => 'Europe/Madrid',
            self::TIMEZONE_NEW_YORK => 'America/New_York',
            self::TIMEZONE_BRASIL => 'America/Sao_Paulo',
            self::TIMEZONE_BUENOS_AIRES => 'America/Argentina/Buenos_Aires',
        ];
    }

    public static function localeOptions(): array
    {
        return [
            self::LOCALE_ES_CO => 'es_CO',
            self::LOCALE_ES_ES => 'es_ES',
        ];
    }

    public static function currencyByCountry(): array
    {
        return [
            self::COUNTRY_CO => 'COP',
            self::COUNTRY_ES => 'EUR',
        ];
    }

    public static function timezoneByCountry(): array
    {
        return [
            self::COUNTRY_CO => 'America/Bogota',
            self::COUNTRY_ES => 'Europe/Madrid',
        ];
    }

    public static function localeByCountry(): array
    {
        return [
            self::COUNTRY_CO => 'es_CO',
            self::COUNTRY_ES => 'es_ES',
        ];
    }

    public function getSportLabelAttribute(): string
    {
        return static::sportOptions()[$this->sport] ?? 'Sin deporte';
    }

    public function getCountryLabelAttribute(): string
    {
        return static::countryOptions()[$this->country] ?? 'Sin país';
    }
}
