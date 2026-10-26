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

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\L10n\Cl\Rut\DataProcessor\RutRuleRegistrar;
use Derafu\L10n\Cl\Rut\Rut;
use Derafu\L10n\Cl\Rut\Translation\L10nClRutTranslationResourceProvider;
use Derafu\Translation\Exception\Runtime\TranslatableUnexpectedValueException;
use Derafu\Translation\TranslatorFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * The standalone provider and optional Data Processor wiring use their real YAML.
 */
#[CoversNothing]
final class ServicesTest extends TestCase
{
    private const TRANSLATED_ERROR = 'El dígito verificador debe ser un carácter entre "0" y "9", o la letra mayúscula "K". Se encontró el valor "X".';

    private function container(bool $rules = false): ContainerBuilder
    {
        $root = dirname(__DIR__, 2);
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator($root . '/resources/config'));
        $loader->load('l10n-cl-rut-services.yaml');
        if ($rules) {
            $loader->load($root . '/vendor/derafu/data-processor/resources/config/data-processor-services.yaml');
            $loader->load('l10n-cl-rut-data-processor-services.yaml');
            $this->assertArrayHasKey(RutRuleRegistrar::class, $container->findTaggedServiceIds('derafu_data_processor.rule_registrar'));
        }
        $this->assertArrayHasKey(
            L10nClRutTranslationResourceProvider::class,
            $container->findTaggedServiceIds('derafu_translation.resource_provider')
        );
        $container->register('app.translator', Translator::class)
            ->setFactory([TranslatorFactory::class, 'create'])
            ->setArguments(['es', ['en'], new TaggedIteratorArgument('derafu_translation.resource_provider')]);
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
        $container->compile();

        return $container;
    }

    public function testStandaloneConfigurationConstructsOnlyTheTranslationProvider(): void
    {
        $container = $this->container();
        $this->assertFalse($container->has(RutRuleRegistrar::class));
        $provider = $container->get(L10nClRutTranslationResourceProvider::class);
        $this->assertInstanceOf(L10nClRutTranslationResourceProvider::class, $provider);
        $directories = [...$provider->getDirectories()];
        $this->assertCount(1, $directories);
        $this->assertSame(realpath(dirname(__DIR__, 2) . '/resources/translations'), realpath($directories[0]));
    }

    public function testTaggedResourcesTranslateARealRutValidationException(): void
    {
        $translator = $this->container()->get('app.translator');
        $this->assertInstanceOf(Translator::class, $translator);
        try {
            Rut::validate('12.345.678-X');
            $this->fail('An invalid verification digit was accepted.');
        } catch (TranslatableUnexpectedValueException $exception) {
            $this->assertSame(self::TRANSLATED_ERROR, $exception->trans($translator));
        }
    }

    public function testTaggedRegistrarAddsRutValidationWithoutReplacingDefaultRules(): void
    {
        $container = $this->container(rules: true);
        $this->assertInstanceOf(RutRuleRegistrar::class, $container->get(RutRuleRegistrar::class));
        $processor = $container->get(ProcessorInterface::class);
        $this->assertInstanceOf(ProcessorInterface::class, $processor);
        $this->assertSame('76.192.083-9', $processor->process('76.192.083-9', ['validate' => ['required', 'rut']]));
        $translator = $container->get('app.translator');
        $this->assertInstanceOf(Translator::class, $translator);
        try {
            $processor->process('12.345.678-X', ['validate' => ['required', 'rut']]);
            $this->fail('The configured processor accepted an invalid RUT.');
        } catch (ValidationException $exception) {
            $this->assertInstanceOf(TranslatableUnexpectedValueException::class, $exception->getPrevious());
            $this->assertSame(self::TRANSLATED_ERROR, $exception->trans($translator));
        }
    }
}
