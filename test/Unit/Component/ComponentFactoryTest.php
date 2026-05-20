<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Component;

use Horde\Icalendar\Component\ComponentFactory;
use Horde\Icalendar\Component\ComponentType;
use Horde\Icalendar\Component\GenericComponent;
use Horde\Icalendar\Property\PropertyBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentFactory::class)]
#[CoversClass(GenericComponent::class)]
class ComponentFactoryTest extends TestCase
{
    private ComponentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ComponentFactory();
    }

    // =========================================================================
    // create() by string
    // =========================================================================

    public function testCreateByString(): void
    {
        $component = $this->factory->create('VEVENT');

        $this->assertInstanceOf(GenericComponent::class, $component);
        $this->assertSame('VEVENT', $component->getType());
    }

    public function testCreateByStringUppercases(): void
    {
        $component = $this->factory->create('vevent');

        $this->assertSame('VEVENT', $component->getType());
    }

    public function testCreateWithPropertyBag(): void
    {
        $bag = new PropertyBag();
        $bag->add('UID', 'test@example.com');

        $component = $this->factory->create('VEVENT', $bag);

        $this->assertSame('test@example.com', $component->getPropertyValue('UID'));
    }

    public function testCreateUnknownType(): void
    {
        $component = $this->factory->create('X-CUSTOM');

        $this->assertInstanceOf(GenericComponent::class, $component);
        $this->assertSame('X-CUSTOM', $component->getType());
    }

    // =========================================================================
    // createFromType() by enum
    // =========================================================================

    public function testCreateFromTypeVCalendar(): void
    {
        $component = $this->factory->createFromType(ComponentType::VCalendar);

        $this->assertSame('VCALENDAR', $component->getType());
    }

    public function testCreateFromTypeVevent(): void
    {
        $component = $this->factory->createFromType(ComponentType::Vevent);

        $this->assertSame('VEVENT', $component->getType());
    }

    public function testCreateFromTypeVtodo(): void
    {
        $component = $this->factory->createFromType(ComponentType::Vtodo);

        $this->assertSame('VTODO', $component->getType());
    }

    public function testCreateFromTypeValarm(): void
    {
        $component = $this->factory->createFromType(ComponentType::Valarm);

        $this->assertSame('VALARM', $component->getType());
    }

    public function testCreateFromTypeVCard(): void
    {
        $component = $this->factory->createFromType(ComponentType::VCard);

        $this->assertSame('VCARD', $component->getType());
    }

    public function testCreateFromTypeWithPropertyBag(): void
    {
        $bag = new PropertyBag();
        $bag->add('VERSION', '2.0');

        $component = $this->factory->createFromType(ComponentType::VCalendar, $bag);

        $this->assertSame('2.0', $component->getPropertyValue('VERSION'));
    }

    // =========================================================================
    // All enum types produce valid components
    // =========================================================================

    public function testAllComponentTypesCreateSuccessfully(): void
    {
        foreach (ComponentType::cases() as $type) {
            $component = $this->factory->createFromType($type);
            $this->assertSame($type->value, $component->getType());
        }
    }
}
