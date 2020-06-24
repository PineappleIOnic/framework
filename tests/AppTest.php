<?php
/**
 * Utopia PHP Framework
 *
 * @package Framework
 * @subpackage Tests
 *
 * @link https://github.com/utopia-php/framework
 * @author Appwrite Team <team@appwrite.io>
 * @version 1.0 RC4
 * @license The MIT License (MIT) <http://www.opensource.org/licenses/mit-license.php>
 */

namespace Utopia;

use PHPUnit\Framework\TestCase;
use Utopia\Validator\Text;

class AppTest extends TestCase
{
    /**
     * @var App
     */
    protected $app = null;

    public function setUp()
    {
        $this->app = new App('Asia/Tel_Aviv', App::MODE_TYPE_PRODUCTION);
    }

    public function testIsMode()
    {

        $this->assertEquals(App::MODE_TYPE_PRODUCTION, $this->app->getMode());
        $this->assertEquals(true, $this->app->isProduction());
        $this->assertEquals(false, $this->app->isDevelopment());
        $this->assertEquals(false, $this->app->isStage());

        $this->app->setMode(App::MODE_TYPE_PRODUCTION);

        $this->assertEquals(App::MODE_TYPE_PRODUCTION, $this->app->getMode());
        $this->assertEquals(true, $this->app->isProduction());
        $this->assertEquals(false, $this->app->isDevelopment());
        $this->assertEquals(false, $this->app->isStage());

        $this->app->setMode(App::MODE_TYPE_DEVELOPMENT);

        $this->assertEquals(App::MODE_TYPE_DEVELOPMENT, $this->app->getMode());
        $this->assertEquals(false, $this->app->isProduction());
        $this->assertEquals(true, $this->app->isDevelopment());
        $this->assertEquals(false, $this->app->isStage());

        $this->app->setMode(App::MODE_TYPE_STAGE);

        $this->assertEquals(App::MODE_TYPE_STAGE, $this->app->getMode());
        $this->assertEquals(false, $this->app->isProduction());
        $this->assertEquals(false, $this->app->isDevelopment());
        $this->assertEquals(true, $this->app->isStage());
    }

    public function testGetEnv()
    {
        // Mock
        $_SERVER['key'] = 'value';

        $this->assertEquals($this->app->getEnv('key'), 'value');
        $this->assertEquals($this->app->getEnv('unknown', 'test'), 'test');
    }

    public function testExecute()
    {
        $this->app->error(function() {
            echo 'error';
        });

        // Default Params
        $route = new Route('GET', '/path');

        $route
            ->param('x', 'x-def', new Text(200), 'x param', false)
            ->param('y', 'y-def', new Text(200), 'y param', false)
            ->action(function($x, $y) {
                echo $x.'-',$y;
            })
        ;

        \ob_start();
        $this->app->execute($route, []);
        $result = \ob_get_contents();
        \ob_end_clean();

        $this->assertEquals('x-def-y-def', $result);

        // With Params

        $route = new Route('GET', '/path');

        $route
            ->param('x', 'x-def', new Text(200), 'x param', false)
            ->param('y', 'y-def', new Text(200), 'y param', false)
            ->action(function($x, $y) {
                echo $x.'-',$y;
            })
        ;

        \ob_start();
        $this->app->execute($route, ['x' => 'param-x', 'y' => 'param-y']);
        $result = \ob_get_contents();
        \ob_end_clean();

        $this->assertEquals('param-x-param-y', $result);

        // With Error

        $route = new Route('GET', '/path');

        $route
            ->param('x', 'x-def', new Text(1), 'x param', false)
            ->param('y', 'y-def', new Text(1), 'y param', false)
            ->action(function($x, $y) {
                echo $x.'-',$y;
            })
        ;

        \ob_start();
        $this->app->execute($route, ['x' => 'param-x', 'y' => 'param-y']);
        $result = \ob_get_contents();
        \ob_end_clean();

        $this->assertEquals('error', $result);

        // With Hooks

        $this->app->init(function() {
            echo 'init-';
        });

        $this->app->shutdown(function() {
            echo '-shutdown';
        });

        $route = new Route('GET', '/path');

        $route
            ->param('x', 'x-def', new Text(200), 'x param', false)
            ->param('y', 'y-def', new Text(200), 'y param', false)
            ->action(function($x, $y) {
                echo $x.'-',$y;
            })
        ;

        \ob_start();
        $this->app->execute($route, ['x' => 'param-x', 'y' => 'param-y']);
        $result = \ob_get_contents();
        \ob_end_clean();

        $this->assertEquals('init-param-x-param-y-shutdown', $result);
    }

    public function tearDown()
    {
        $this->view = null;
    }
}