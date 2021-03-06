<?php

namespace Kunstmaan\PagePartBundle\Tests\PagePartConfigurationReader;

use Codeception\Test\Unit;
use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdminConfiguratorInterface;
use Kunstmaan\PagePartBundle\PagePartConfigurationReader\PagePartConfigurationParser;
use Symfony\Component\HttpKernel\KernelInterface;

class PagePartConfigurationParserTest extends Unit
{
    public function testParseSf4Flow()
    {
        $kernel = $this->makeEmpty(KernelInterface::class);
        $pagePartConfigurationParser = new PagePartConfigurationParser($kernel, [
            'main' => [
                'name' => 'Main content',
                'context' => 'main',
                'types' => [
                    ['name' => 'FooBar', 'class' => 'Foo\BarPagePart'],
                    ['name' => 'Foo', 'class' => 'FooPagePart'],
                    ['name' => 'Bar', 'class' => 'BarPagePart'],
                ],
            ],
        ]);

        $result = $pagePartConfigurationParser->parse('main');
        $this->assertInstanceOf(PagePartAdminConfiguratorInterface::class, $result);
        $this->assertEquals('Main content', $result->getName());
    }

    public function testParseSf3Flow()
    {
        $kernel = $this->makeEmpty(KernelInterface::class, [
            'locateResource' => __DIR__ . '/Resources/config/pageparts/main.yml',
        ]);

        $pagePartConfigurationParser = new PagePartConfigurationParser($kernel, []);

        $result = $pagePartConfigurationParser->parse('MyWebsiteBundle:main');
        $this->assertInstanceOf(PagePartAdminConfiguratorInterface::class, $result);
        $this->assertEquals('Main content', $result->getName());
    }

    public function testPresetExtendsBundle()
    {
        $kernel = $this->makeEmpty(KernelInterface::class);
        $pagePartConfigurationParser = new PagePartConfigurationParser($kernel, [
            'foo' => [
                'name' => 'Foo content',
                'context' => 'foo',
                'extends' => 'main',
                'types' => [
                    ['name' => 'FooBar', 'class' => 'Foo\BarPagePart'],
                    ['name' => 'Foo', 'class' => 'FooPagePart'],
                    ['name' => 'Bar', 'class' => 'BarPagePart'],
                ],
            ],
            'main' => [
                'name' => 'Main content',
                'context' => 'main',
                'types' => [
                    ['name' => 'Header', 'class' => 'HeaderPagePart'],
                ],
            ],
        ]
        );

        $value = $pagePartConfigurationParser->parse('foo');

        $this->assertCount(4, $value->getPossiblePagePartTypes());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCircularReferenceIsDetected()
    {
        $kernel = $this->makeEmpty(KernelInterface::class);

        $parser = new PagePartConfigurationParser($kernel, [
            'foo' => [
                'name' => 'Foo preset',
                'extends' => 'bar',
            ],
            'bar' => [
                'name' => 'Bar preset',
                'extends' => 'baz',
            ],
            'baz' => [
                'name' => 'Baz preset',
                'extends' => 'foo',
            ],
        ]);

        $parser->parse('foo');
    }
}
