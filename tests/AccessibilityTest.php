<?php

namespace Frog\Tests;

class AccessibilityTest extends TestCase
{
    public function run(): void
    {
        $root = dirname(__DIR__);
        $docsBase = file_get_contents($root . '/framework/Views/docs/base.php') ?: '';
        $landingLayout = file_get_contents($root . '/framework/Views/Layout/landing.php') ?: '';

        $this->assertTrue(str_contains($docsBase, '<html lang="en"'), 'Docs base should declare html lang');
        $this->assertTrue(str_contains($docsBase, 'name="viewport"'), 'Docs base should include a viewport meta tag');
        $this->assertTrue(str_contains($docsBase, 'aria-current'), 'Docs base should manage aria-current for nav/toc');

        $this->assertTrue(
            stripos($landingLayout, 'alt="') !== false,
            'Landing layout images should include alt text'
        );
    }
}
