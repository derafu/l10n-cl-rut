<?php

declare(strict_types=1);

/**
 * Derafu: L10n CL RUT - Chilean RUT library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

return [
    'The RUT cannot be less than {minimum} and the value {value} was found.' =>
        'El RUT no puede ser menor que {minimum} y se encontró el valor {value}.',
    'The RUT cannot be greater than {maximum} and the value {value} was found.' =>
        'El RUT no puede ser mayor que {maximum} y se encontró el valor {value}.',
    'The verification digit must be a character between "0" and "9", or the uppercase letter "K". The value "{digit}" was found.' =>
        'El dígito verificador debe ser un carácter entre "0" y "9", o la letra mayúscula "K". Se encontró el valor "{digit}".',
    'The verification digit of the RUT {rut} is incorrect. The value "{digit}" was found and for the numeric part {number} of the RUT, the verification digit should be "{expected}".' =>
        'El dígito verificador del RUT {rut} es incorrecto. Se encontró el valor "{digit}" y para la parte numérica {number} del RUT, el dígito verificador debería ser "{expected}".',
];
