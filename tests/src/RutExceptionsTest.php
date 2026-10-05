<?php

declare(strict_types=1);

/**
 * Derafu: L10n CL RUT - Chilean RUT library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\L10n\Cl\TestsRut;

use Derafu\L10n\Cl\Rut\Rut;
use Derafu\Translation\Contract\TranslatableInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use UnexpectedValueException;

/**
 * An invalid RUT is reported with a translatable error that says the same as
 * it always did.
 */
#[CoversClass(Rut::class)]
final class RutExceptionsTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidRutProvider(): array
    {
        return [
            'below the minimum' => [
                '999.999-9',
                'The RUT cannot be less than 1.000.000 and the value 999.999 was found.',
            ],
            'above the maximum' => [
                '100.000.000-0',
                'The RUT cannot be greater than 99.999.999 and the value 100.000.000 was found.',
            ],
            'verification digit that is not valid' => [
                '12.345.678-X',
                'The verification digit must be a character between "0" and "9", or the uppercase letter "K". The value "X" was found.',
            ],
            'verification digit that is not correct' => [
                '12.345.678-K',
                'The verification digit of the RUT 12.345.678-K is incorrect. The value "K" was found and for the numeric part 12.345.678 of the RUT, the verification digit should be "5".',
            ],
        ];
    }

    #[DataProvider('invalidRutProvider')]
    public function testAnInvalidRutIsATranslatableErrorThatSaysTheSame(string $rut, string $message): void
    {
        $exception = null;
        try {
            Rut::validate($rut);
        } catch (Throwable $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(UnexpectedValueException::class, $exception);
        $this->assertInstanceOf(TranslatableInterface::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
