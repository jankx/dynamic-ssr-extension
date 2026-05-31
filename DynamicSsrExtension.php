<?php

namespace Jankx\Extensions\DynamicSsr;

use Jankx\Extensions\AbstractExtension;

class DynamicSsrExtension extends AbstractExtension
{
    public function init(): void
    {
    }

    public function register_hooks(): void
    {
        add_action('jankx/gutenberg/register-blocks', [$this, 'register_extension_blocks'], 10, 2);
        add_filter('jankx/view_service/paths', [$this, 'register_template_paths'], 5, 1);
        add_filter('jankx/renderer/ssr_generator_class', [$this, 'register_ssr_generator_class'], 10, 1);
    }

    /**
     * Register the extension's templates directory with ViewService,
     * so post-layout templates (grid, carousel, list, masonry, item) are
     * resolved from this extension before falling through to the framework defaults.
     *
     * @param array $paths Existing search paths
     * @return array
     */
    public function register_template_paths(array $paths): array
    {
        $templates_dir = $this->get_extension_path() . '/templates';
        if (is_dir($templates_dir) && !in_array($templates_dir, $paths, true)) {
            array_unshift($paths, $templates_dir);
        }
        return $paths;
    }

    /**
     * Provide the SSR generator class to the framework Renderer.
     * Called via filter `jankx/renderer/ssr_generator_class`.
     *
     * @param string|null $class Currently registered class (null = none yet)
     * @return string Fully-qualified class name
     */
    public function register_ssr_generator_class(?string $class): string
    {
        require_once __DIR__ . '/includes/Generators/SsrViewGenerator.php';
        return \Jankx\Extensions\DynamicSsr\Generators\SsrViewGenerator::class;
    }

    public function register_extension_blocks($repository, $app): void
    {
        $blocks = ["DynamicSsrLayoutBlock", "DynamicSsrTemplateBlock"];

        foreach ($blocks as $blockClass) {
            require_once __DIR__ . '/includes/Blocks/' . $blockClass . '.php';
            $fullClass = 'Jankx\\Extensions\\DynamicSsr\\Blocks\\' . $blockClass;
            $block = $app->make($fullClass);
            $blockId = basename($block->getBlockId());
            $block->setBlockPath($this->get_extension_path() . '/assets/blocks/' . $blockId);
            $repository->registerBlock($block);
        }
    }
}
