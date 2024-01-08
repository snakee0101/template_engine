<?php

include 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Blade\Core\TemplateEngine;

class TemplateEngineTest extends TestCase
{
    public function test_config_could_be_loaded_into_session()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        $this->assertEquals('/compiled', $_SESSION['config']['compiled_path']);
    }

    public function test_config_value_is_returned()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        $this->assertEquals('/compiled', $e->config('compiled_path'));
    }

    public function test_config_value_could_be_temporary_replaced()
    {
        $e = new TemplateEngine;

        $e->loadConfig();
        $e->config('compiled_path', '/temporary_path');

        $this->assertEquals('/temporary_path', $e->config('compiled_path')); //value is temporary replaced
        $this->assertEquals('/temporary_path', $_SESSION['config']['compiled_path']);

        $e->loadConfig();
        $this->assertEquals('/compiled', $e->config('compiled_path')); //but it doesn't affect original value in the file
        $this->assertEquals('/compiled', $_SESSION['config']['compiled_path']);
    }

    public function test_if_view_is_not_found_exception_is_thrown()
    {
        $this->expectExceptionMessage('view 12345 was not found');
        $e = new TemplateEngine;
        $e->loadConfig();

        $e->view('12345');
    }

    public function test_view_content_could_be_returned()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        $expected = <<<'CONTENT'
        <p>
        <?php
            $a = 7**2;
            $b = 3;
            $arr = [5, 5, 5, 'abc'];
            echo "The result of calculation is: " . $a + $b;
        ?>
        </p>
        <p>This paragraph contains the value of 'abc' GET-parameter <?php echo $_GET['abc']; ?></p>
        <h2>Next is print_r()</h2>
        <?php print_r($arr); ?>
        CONTENT;

        file_put_contents(__DIR__ . "/../compiled/test_view_123123124.blade.php", $expected);

        ob_start(); //to intercept require call, burefization will be used
        $e->view('test_view_123123124'); //view is "required", not returned - so the variable values could be tested
        $result = ob_get_clean();

        $this->assertTrue(str_contains($result, "The result of calculation is: 52"));
    }

    public function test_variables_outside_the_view_must_not_be_available_to_it()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        $expected = <<<'CONTENT'
        <?php
            echo $view_path;
        ?>
        CONTENT;

        file_put_contents(__DIR__ . "/../compiled/test_view_1231000000.blade.php", $expected);

        ob_start();
        $e->view('test_view_1231000000');
        $result = ob_get_clean();

        $this->assertFalse(str_contains($result, 'test_view_1231000000.blade.php'));
    }

    public function test_variables_could_be_passed_to_the_view()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        $expected = <<<'CONTENT'
        <?php
            echo "$parameter_1 $parameter_2[1]";
        ?>
        CONTENT;

        file_put_contents(__DIR__ . "/../compiled/test_view_1231000000.blade.php", $expected);

        ob_start();
        $e->view('test_view_1231000000', [
            'parameter_1' => 'test value 1',
            'parameter_2' => ['abc', 78],
        ]);
        $result = ob_get_clean();

        $this->assertTrue(str_contains($result, 'test value 1 78'));
    }

    public function test_view_without_directives_remains_unchanged_when_compiled()
    {
        $e = new TemplateEngine;
        $e->loadConfig();

        file_put_contents($view_path = __DIR__ . "/../views/view_to_compile.blade.php", $content = '
        <p>some text content</p>
        <h2>
        <?php 
            $v = "this is php "
            echo $v . "script"; 
        ?>
        </h2>
        <ul>
        <?php 
           foreach($a = 1; $a < 10; $a++): 
        ?>
        <li><?php echo $a; ?></li>
        <?php endforeach; ?>
        </ul>
        ');

        $this->assertEquals($content, $e->compile($view_path));
    }
}
