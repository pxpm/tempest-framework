<?php

declare(strict_types=1);

namespace Tempest {

    use Psr\Log\LoggerInterface;
    use ReflectionType;
    use Reflector;
    use Stringable;
    use Symfony\Component\VarDumper\VarDumper;
    use Tempest\Container\GenericContainer;
    use Tempest\Events\EventBus;
    use Tempest\Mapper\ObjectFactory;
    use Tempest\Support\Reflection\Attributes;
    use Tempest\Support\Reflection\TypeName;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className): object
    {
        $container = GenericContainer::instance();

        return $container->get($className);
    }

    function event(object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeName
     * @return \Tempest\Support\Reflection\Attributes<T>
     */
    function attribute(string $attributeName): Attributes
    {
        return Attributes::find($attributeName);
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return ObjectFactory<T>
     */
    function make(object|string $objectOrClass): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->withData($data);
    }

    function type(Reflector|ReflectionType $reflector): string
    {
        return (new TypeName())->resolve($reflector);
    }

    function lw(mixed ...$input): void
    {
        /** @var LoggerInterface $logger */
        $logger = get(LoggerInterface::class);

        foreach ($input as $key => $item) {
            if ($item instanceof Stringable) {
                $message = (string)$item;
            } else {
                $message = var_export($item, true);
            }

            $logger->debug("[{$key}] {$message}");
        }

        VarDumper::dump(...$input);
    }

    function ld(mixed ...$input): void
    {
        lw(...$input);
        die();
    }
}
