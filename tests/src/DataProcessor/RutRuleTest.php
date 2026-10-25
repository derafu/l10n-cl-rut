<?php

declare(strict_types=1);

/**
 * Derafu: L10n CL RUT - Chilean RUT library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\L10n\Cl\TestsRut\DataProcessor;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\CompositeRuleRegistrar;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\L10n\Cl\Rut\DataProcessor\RutRule;
use Derafu\L10n\Cl\Rut\DataProcessor\RutRuleRegistrar;
use Derafu\L10n\Cl\Rut\Rut;
use Derafu\L10n\Cl\Rut\Translation\L10nClRutTranslationResourceProvider;
use Derafu\Translation\Contract\TranslatableInterface;
use Derafu\Translation\TranslatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * The rule `rut` validates a Chilean RUT or RUN in the data processor.
 */
#[CoversClass(RutRule::class)]
#[CoversClass(RutRuleRegistrar::class)]
#[CoversClass(Rut::class)]
#[UsesClass(L10nClRutTranslationResourceProvider::class)]
final class RutRuleTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function validRutProvider(): array
    {
        return [
            'with dots and dash' => ['76.192.083-9'],
            'with dash' => ['76192083-9'],
            'without separators' => ['761920839'],
            'with the digit K' => ['10.000.013-K'],
            'with a lowercase K' => ['10.000.013-k'],
        ];
    }

    #[DataProvider('validRutProvider')]
    public function testAValidRutPasses(string $rut): void
    {
        (new RutRule())->validate($rut);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidRutProvider(): array
    {
        return [
            'digit that is not correct' => [
                '76192083-1',
                'The verification digit of the RUT 76.192.083-1 is incorrect. The value "1" was found and for the numeric part 76.192.083 of the RUT, the verification digit should be "9".',
            ],
            'below the minimum' => [
                '999.999-9',
                'The RUT cannot be less than 1.000.000 and the value 999.999 was found.',
            ],
            'digit that is not valid' => [
                '12.345.678-X',
                'The verification digit must be a character between "0" and "9", or the uppercase letter "K". The value "X" was found.',
            ],
        ];
    }

    #[DataProvider('invalidRutProvider')]
    public function testAnInvalidRutFailsWithTheMessageOfTheRut(string $rut, string $message): void
    {
        $exception = null;
        try {
            (new RutRule())->validate($rut);
        } catch (ValidationException $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertInstanceOf(UnexpectedValueException::class, $exception->getPrevious());
    }

    #[DataProvider('invalidRutProvider')]
    public function testTheErrorIsTranslated(string $rut, string $message): void
    {
        $translator = TranslatorFactory::create(
            'es',
            ['en'],
            [new L10nClRutTranslationResourceProvider()]
        );

        $exception = null;
        try {
            (new RutRule())->validate($rut);
        } catch (ValidationException $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertNotSame($message, $exception->trans($translator));
        $previous = $exception->getPrevious();
        $this->assertInstanceOf(TranslatableInterface::class, $previous);
        $this->assertSame($previous->trans($translator), $exception->trans($translator));
    }

    public function testTheErrorOfAValueThatIsNotAStringIsTranslated(): void
    {
        $translator = TranslatorFactory::create(
            'es',
            ['en'],
            [new L10nClRutTranslationResourceProvider()]
        );

        $exception = null;
        try {
            (new RutRule())->validate(12345678);
        } catch (ValidationException $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame('Value must be a string.', $exception->getMessage());
        $this->assertSame('El valor debe ser una cadena.', $exception->trans($translator));
    }

    public function testTheRegistrarAddsTheRule(): void
    {
        $registry = new RuleRegistry();

        (new RutRuleRegistrar())->register($registry);

        $this->assertInstanceOf(RutRule::class, $registry->getValidatorRule('rut'));
    }

    public function testTheProcessorValidatesWithTheRule(): void
    {
        $processor = ProcessorFactory::create(new CompositeRuleRegistrar([new RutRuleRegistrar()]));

        $this->assertSame('76192083-9', $processor->process('76192083-9', ['validate' => ['rut']]));
        $this->assertSame('76192083-9', $processor->process('76192083-9', ['validate' => 'required|rut']));

        // An empty value is allowed unless the field is required.
        $this->assertNull($processor->process('', ['validate' => ['rut']]));

        $this->expectException(ValidationException::class);
        $processor->process('76192083-1', ['validate' => ['rut']]);
    }
}
