<?php
/**
 * Request Test case file.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Routing.Route
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Routing\Route;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Response;
use Cake\Routing\Route\RedirectRoute;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * test case for RedirectRoute
 *
 * @package       Cake.Test.Case.Routing.Route
 */
class RedirectRouteTest extends TestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		Configure::write('Routing', array('admin' => null, 'prefixes' => array()));
		Router::reload();
	}

/**
 * test the parsing of routes.
 *
 * @return void
 */
	public function testParsing() {
		Router::connect('/:controller', array('action' => 'index'));
		Router::connect('/:controller/:action/*');

		$route = new RedirectRoute('/home', array('controller' => 'posts'));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/home');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/posts', true), $header['Location']);

		$route = new RedirectRoute('/home', array('controller' => 'posts', 'action' => 'index'));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/home');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/posts', true), $header['Location']);
		$this->assertEquals(301, $route->response->statusCode());

		$route = new RedirectRoute('/google', 'http://google.com');
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/google');
		$header = $route->response->header();
		$this->assertEquals('http://google.com', $header['Location']);

		$route = new RedirectRoute('/posts/*', array('controller' => 'posts', 'action' => 'view'), array('status' => 302));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/posts/2');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/posts/view', true), $header['Location']);
		$this->assertEquals(302, $route->response->statusCode());

		$route = new RedirectRoute('/posts/*', array('controller' => 'posts', 'action' => 'view'), array('persist' => true));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/posts/2');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/posts/view/2', true), $header['Location']);

		$route = new RedirectRoute('/posts/*', '/test', array('persist' => true));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/posts/2');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/test', true), $header['Location']);

		$route = new RedirectRoute('/my_controllers/:action/*', array('controller' => 'tags', 'action' => 'add'), array('persist' => true));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/my_controllers/do_something/passme');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/tags/add/passme', true), $header['Location']);

		$route = new RedirectRoute('/my_controllers/:action/*', array('controller' => 'tags', 'action' => 'add'));
		$route->stop = false;
		$route->response = $this->getMock('Cake\Network\Response', array('_sendHeader'));
		$result = $route->parse('/my_controllers/do_something/passme');
		$header = $route->response->header();
		$this->assertEquals(Router::url('/tags/add', true), $header['Location']);
	}

}
