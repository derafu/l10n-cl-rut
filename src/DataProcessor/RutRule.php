<?php

declare(strict_types=1);

/**
 * Derafu: L10n CL RUT - Chilean RUT library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\L10n\Cl\Rut\DataProcessor;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\L10n\Cl\Rut\Rut;
use Derafu\Translation\Exception\Runtime\TranslatableUnexpectedValueException;

/**
 * Validator rule `rut`: the value must be a valid Chilean RUT or RUN, with its
 * verification digit (dots and dash optional).
 *
 * It needs `derafu/data-processor`, which is optional for this package.
 *
 * The message of the error is the one of {@see Rut::validate()}, so it is
 * translated with the translations of this package.
 */
final class RutRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array<int|string, mixed> $parameters The rule has no parameters.
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        try {
            Rut::validate($value);
        } catch (TranslatableUnexpectedValueException $e) {
            throw new ValidationException($e, previous: $e);
        }
    }
}
