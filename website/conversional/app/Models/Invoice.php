<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    const REGISTRATION = 'registration';
    const ACTIVATED = 'activated';
    const APPOINTMENT = 'appointment';

    const REGISTRATION_PRICE = 0.49;
    const ACTIVATED_PRICE = 0.99;
    const APPOINTMENT_PRICE = 3.99;

    const PRICE_DESCRIPTION_REGISTRATION = "user should pay this for first registrarion -(" . self::REGISTRATION_PRICE . ")";
    const PRICE_DESCRIPTION_ACTIVATED = "user should pay this for first activation -(" . self::ACTIVATED_PRICE . ")";
    const PRICE_DESCRIPTION_APPOINTMENT = "user should pay this for first appointment -(" . self::APPOINTMENT_PRICE . ")";

    const PRICE_DESCRIPTION_FROM_REGISTRATION_TO_ACTIVATED = "user should pay this for activation but not complete bucause user payed for registration -(" . self::ACTIVATED_PRICE . " - " . self::REGISTRATION_PRICE . ")";
    const PRICE_DESCRIPTION_FROM_REGISTRATION_TO_APPOINTMENT = "user should pay this for appointment but not complete bucause user payed for registration -(" . self::APPOINTMENT_PRICE . " - " . self::REGISTRATION_PRICE . ")";

    const PRICE_DESCRIPTION_FROM_ACTIVATED_TO_APPOINTMENT = "user should pay this for appointment but not complete bucause user payed for activation -(" . self::APPOINTMENT_PRICE . " - " . self::ACTIVATED_PRICE . ")";

    use HasFactory;

    public $timestamps = false;
}
