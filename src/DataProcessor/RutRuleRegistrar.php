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

use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;

/**
 * Registers the rules of this package in the registry of `derafu/data-processor`:
 *
 *   - `rut`: the value must be a valid Chilean RUT or RUN.
 *
 * With the container, tag it with `derafu_data_processor.rule_registrar` (see
 * `resources/config/l10n-cl-rut-data-processor-services.yaml`).
 */
final class RutRuleRegistrar implements RuleRegistrarInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(RuleRegistryInterface $registry): void
    {
        $registry->addValidatorRule('rut', RutRule::class);
    }
}
