<?php

include 'vendor/autoload.php';

use Blade\Core\TemplateEngine;
use PHPUnit\Framework\TestCase;
use Blade\Core\ViewsMapper;

class ViewsMapperTest extends TestCase
{
    public $templateEngine;

    protected function setUp(): void
    {
        $this->templateEngine = new TemplateEngine();
        $this->templateEngine->loadConfig();
    }

    /**
     * Note: When referencing $sub_directory, always add / at the end and at the beginning like "/admin/"
     */
    private function generate_random_view($filename, $extension, $sub_directory = '/')
    {
        $file_path = __DIR__ .
            '/..' .
            $this->templateEngine->config('views_directory') .
            $sub_directory .
            $filename .
            $extension;

        file_put_contents($file_path, 'test');

        return $file_path;
    }

    public function test_mapper_finds_views_in_base_directory()
    {
        //create fake files in /views directory (including non-view extension)   (3 views, 2 non-views)
        $file_paths = [
            $this->generate_random_view('test_1', $this->templateEngine->config('view_extension')),
            $this->generate_random_view('test_2', $this->templateEngine->config('view_extension')),
            $this->generate_random_view('test_3', $this->templateEngine->config('view_extension')),

            $this->generate_random_view('test_4', '.php'),  //these two should not be included in view mapper result
            $this->generate_random_view('test_5', '.php')
        ];

        //test the result of a mapper
        $files = (new ViewsMapper)->run();

        foreach ($files as $view_path => $compiled_path) 
        {
            $this->assertTrue(str_contains($view_path, 'test_1.blade.php') ||
                str_contains($view_path, 'test_2.blade.php') ||
                str_contains($view_path, 'test_3.blade.php'));

            $this->assertFalse(str_contains($view_path, 'test_4.blade.php') ||
                str_contains($view_path, 'test_5.blade.php'));
        }

        //delete all fake files
        foreach ($file_paths as $path) {
            unlink($path);
        }
    }

    public function test_mapper_finds_views_in_specified_directory()
    {
        //create fake files in /views and /views/sub directories  (3 views, 2 non-views)
        mkdir($sub_directory_path = __DIR__ . '/..' . $this->templateEngine->config('views_directory') . '/sub');
        $file_paths = [
            $this->generate_random_view('test_1', $this->templateEngine->config('view_extension')),
            $this->generate_random_view('test_2', $this->templateEngine->config('view_extension'), '/sub/')
        ];

        //test the result of a mapper
        $files = (new ViewsMapper("/views/sub"))->run();

        $this->assertCount(1, $files);
        $view_path = array_keys($files)[0]; //first item's key contains the path to the original view

        $this->assertTrue(str_contains($view_path, 'views/sub/test_2.blade.php'));

        //delete all fake files
        foreach ($file_paths as $path) {
            unlink($path);
        }
        rmdir($sub_directory_path);
    }

    public function test_mapper_finds_views_recursively()
    {
        //create fake files in /views, /views/sub, and views/sub/sub2 directories  (3 views)
        mkdir($sub_directory_path = __DIR__ . '/..' . $this->templateEngine->config('views_directory') . '/sub');
        mkdir($sub_directory_path_2 = __DIR__ . '/..' . $this->templateEngine->config('views_directory') . '/sub/sub2');

        $file_paths = [
            $this->generate_random_view('test_1', $this->templateEngine->config('view_extension')),
            $this->generate_random_view('test_2', $this->templateEngine->config('view_extension'), '/sub/'),
            $this->generate_random_view('test_3', $this->templateEngine->config('view_extension'), '/sub/sub2/'),
        ];

        //test the result of a mapper
        $files = (new ViewsMapper)->run();
        $this->assertCount(3, $files);
        
        $test_str_views = implode(',', array_keys($files));
        $test_str_compiled = implode(',', $files);

        $this->assertStringContainsString('/views/test_1.blade.php', $test_str_views);
        $this->assertStringContainsString('/views/sub/test_2.blade.php', $test_str_views);
        $this->assertStringContainsString('/views/sub/sub2/test_3.blade.php', $test_str_views);

        $this->assertStringContainsString('/compiled/test_1.blade.php', $test_str_compiled);
        $this->assertStringContainsString('/compiled/sub/test_2.blade.php', $test_str_compiled);
        $this->assertStringContainsString('/compiled/sub/sub2/test_3.blade.php', $test_str_compiled);

        //delete all fake files
        foreach ($file_paths as $path) {
            unlink($path);
        }

        rmdir($sub_directory_path_2);
        rmdir($sub_directory_path);
    }
}
