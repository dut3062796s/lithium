<?php
/**
 * li₃: the most RAD framework for PHP (http://li3.me)
 *
 * Copyright 2010, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

namespace lithium\tests\cases\console\command;

use lithium\console\command\Help;
use lithium\console\Request;

class HelpTest extends \lithium\test\Unit {

	public $request;

	public $classes = [];

	protected $_backup = [];

	public function setUp() {
		$this->classes = ['response' => 'lithium\tests\mocks\console\MockResponse'];
		$this->_backup['cwd'] = getcwd();
		$this->_backup['_SERVER'] = $_SERVER;
		$_SERVER['argv'] = [];

		$this->request = new Request(['input' => fopen('php://temp', 'w+')]);
		$this->request->params = ['library' => 'build_test'];
	}

	public function tearDown() {
		$_SERVER = $this->_backup['_SERVER'];
		chdir($this->_backup['cwd']);
	}

	public function testRun() {
		$command = new Help(['request' => $this->request, 'classes' => $this->classes]);
		$this->assertTrue($command->run());

		$expected = "COMMANDS via lithium\n";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);

		$expected = preg_quote($expected);
		$result = $command->response->output;
		$pattern = "/\s+test\s+Runs a given set of tests and outputs the results\./ms";
		$this->assertPattern($pattern, $result);
	}

	public function testRunWithName() {
		$command = new Help([
			'request' => $this->request, 'classes' => $this->classes
		]);

		$result = $command->run('Test');
		$this->assertTrue($result);
		$result = $command->run('test');
		$this->assertTrue($result);

		$expected  = 'li3 test [--filters=<string>]';
		$expected .= ' [--format=<string>] [--verbose] [--just-assertions] [--silent] [--plain] [--help] [<path>]';
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);

		$expected = "OPTIONS\n    <path>\n";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);

		$expected = "DESCRIPTION\n";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);

		$expected = "Command `TestWithDashes` not found";
		$expected = preg_quote($expected);
		$result = $command->run('test-with-dashes');
		$this->assertFalse($result);
		$result = $command->response->error;
		$this->assertPattern("/{$expected}/", $result);

		$expected = "Command `TestWithUnderscores` not found";
		$expected = preg_quote($expected);
		$result = $command->run('test_with_underscores');
		$this->assertFalse($result);
		$result = $command->response->error;
		$this->assertPattern("/{$expected}/", $result);
	}

	/**
	 * Tests that class and method help includes detailed descriptions as well as summary text.
	 */
	public function testDocsIncludeDescription() {
		$command = new Help(['request' => $this->request, 'classes' => $this->classes]);

		$this->assertNull($command->api('lithium.core.Libraries'));
		$this->assertPattern('/Auto-loading classes/', $command->response->output);

		$command = new Help(['request' => $this->request, 'classes' => $this->classes]);

		$this->assertNull($command->api('lithium.core.Libraries::add'));
		$this->assertPattern('/Adding libraries/', $command->response->output);
	}

	public function testApiClass() {
		$command = new Help(['request' => $this->request, 'classes' => $this->classes]);
		$result = $command->api('lithium.util.Inflector');
		$this->assertNull($result);

		$expected = "Utility for modifying format of words";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testApiMethod() {
		$command = new Help(['request' => $this->request, 'classes' => $this->classes]);
		$result = $command->api('lithium.util.Inflector', 'method');
		$this->assertNull($result);

		$expected = "rules";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testApiMethodWithName() {
		$command = new Help([
			'request' => $this->request, 'classes' => $this->classes
		]);
		$result = $command->api('lithium.util.Inflector', 'method', 'rules');
		$this->assertNull($result);

		$expected = "rules";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testApiProperty() {
		$command = new Help([
			'request' => $this->request, 'classes' => $this->classes
		]);
		$result = $command->api('lithium.net.Message', 'property');
		$this->assertNull($result);

		$expected = "    --host=<string>\n        The hostname for this endpoint.";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testApiPropertyWithName() {
		$command = new Help([
			'request' => $this->request, 'classes' => $this->classes
		]);
		$result = $command->api('lithium.net.Message', 'property');
		$this->assertNull($result);

		$expected = "    --host=<string>\n        The hostname for this endpoint.";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testApiProperties() {
		$help = new Help([
			'request' => $this->request, 'classes' => $this->classes
		]);
		$expected = null;
		$result = $help->api('lithium.tests.mocks.console.command.MockCommandHelp', 'property');
		$this->assertEqual($expected, $result);

		$expected = "\-\-long=<string>.*\-\-blong.*\-s";
		$result = $help->response->output;
		$this->assertPattern("/{$expected}/s", $result);
	}
}

?>