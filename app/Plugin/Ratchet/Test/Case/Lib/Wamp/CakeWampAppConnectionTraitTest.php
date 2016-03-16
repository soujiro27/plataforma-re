<?php

/**
 * This file is part of Ratchet for CakePHP.
 *
 ** (c) 2012 - 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('AbstractCakeRatchetTestCase', 'Ratchet.Test/Case');

class CakeWampAppConnectionTraitTest extends AbstractCakeRatchetTestCase {

	public function testGetConnections() {
		$mock = $this->getMock('\\Ratchet\\ConnectionInterface');
		$conn = new Ratchet\Wamp\WampConnection($mock);
		$conn->Session = new SessionHandlerImposer();

		$this->assertEquals($this->AppServer->getConnections(), []);

		$this->AppServer->onOpen($conn);

		$this->assertEquals($this->AppServer->getConnections(), [
				$conn->WAMP->sessionId => [
					'topics' => [],
					'session' => $conn->Session->all(),
				],
			]);
	}

	public function testOnOpen() {
		$mock = $this->getMock('\\Ratchet\\ConnectionInterface');
		$conn = new Ratchet\Wamp\WampConnection($mock);
		$conn->Session = new SessionHandlerImposer();

		$asserts = [];
		$this->_expectedEventCalls(
			$asserts,
			[
			'Rachet.WampServer.onOpen' => [
				'callback' => [
					function ($event) use ($conn) {
						$this->assertEquals(
							$event->data,
							[
							'connection' => $conn,
							'wampServer' => $this->AppServer,
							'connectionData' => [
								'topics' => [],
								'session' => [],
							],
							]
						);
					},
				],
			],
			]
		);
		$this->AppServer->onOpen($conn);

		foreach ($asserts as $assert) {
			$this->assertTrue($assert);
		}
	}

	public function testOnClose() {
		$mock = $this->getMock('\\Ratchet\\ConnectionInterface');
		$conn = new Ratchet\Wamp\WampConnection($mock);
		$conn->Session = new SessionHandlerImposer();

		$asserts = [];
		$this->_expectedEventCalls(
			$asserts,
			[
			'Rachet.WampServer.onClose' => [
				'callback' => [
					function ($event) use ($conn) {
						$this->assertEquals(
							$event->data,
							[
							'connection' => $conn,
							'wampServer' => $this->AppServer,
							'connectionData' => [
								'topics' => [],
								'session' => [],
							],
							]
						);
					},
				],
			],
			'Rachet.WampServer.onUnSubscribe.test' => [
				'callback' => [
					function ($event) {
					},
				],
			],
			]
		);
		$this->AppServer->onOpen($conn);
		$this->AppServer->onSubscribe($conn, new \Ratchet\Wamp\Topic('test'));
		$this->AppServer->onClose($conn);

		foreach ($asserts as $assert) {
			$this->assertTrue($assert);
		}
	}
}
