<?php

/**
 * This file is part of Ratchet for CakePHP.
 *
 ** (c) 2012 - 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('Security', 'Utility');
App::uses('CakeWampAppServer', 'Ratchet.Lib/Wamp');
App::uses('PhpSerializeHandler', 'Ratchet.Lib');
App::uses('CakeWampSessionHandler', 'Ratchet.Model/Datasource/Session');
App::uses('RatchetCakeSession', 'Ratchet.Lib');
App::uses('RatchetMessageQueueProxy', 'Ratchet.Lib/MessageQueue/Transports');
App::uses('RatchetMessageQueueModelUpdateCommand', 'RatchetModelPush.Lib/MessageQueue/Command');
App::uses('RatchetMessageQueueKillSwitchCommand', 'RatchetCommands.Lib/MessageQueue/Command');

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

class WebsocketShell extends Shell {

/**
 * The ReactPHP event loop making async programming in PHP possible
 *
 * @var \React\EventLoop\LoopInterface
 */
	private $__loop;

/**
 * The IO server handling the incoming websocket connections
 *
 * @var \Ratchet\Server\IoServer
 */
	private $__ioServer;

/**
 * Starts the websocket server
 *
 * @return void
 */
	public function start() {
		$this->__loop = LoopFactory::create();

		if ($this->__loop instanceof \React\EventLoop\StreamSelectLoop) {
			$this->out(
				'<warning>Your configuration doesn\'t seem to support \'ext-libevent\'. It is highly reccomended that you install and configure it as it provides significant performance gains over stream select!</warning>'
			);
		}

		$socket = new Reactor($this->__loop);
		$socket->listen(
			Configure::read('Ratchet.Connection.websocket.port'),
			Configure::read('Ratchet.Connection.websocket.address')
		);
		$this->__ioServer = new IoServer(
			new HttpServer(
				new WsServer(
					new SessionProvider(
						new WampServer(
							new CakeWampAppServer(
								$this,
								$this->__loop,
								CakeEventManager::instance(),
								$this->params['verbose']
							)
						),
						new CakeWampSessionHandler(),
						[],
						new PhpSerializeHandler()
					)
				)
			),
			$socket,
			$this->__loop
		);

		$this->__loop->run();
	}

/**
 * Stops the websocket server
 *
 * @return void
 */
	public function stop() {
		$command = new RatchetMessageQueueKillSwitchCommand();
		$command->setShell($this);
		$command->setHash(Security::hash(serialize(Configure::read('PhuninCake.Node')), 'sha256', true));

		$this->out('<info>Sending stop command</info>');

		RatchetMessageQueueProxy::instance()->queueMessage($command);
	}

/**
 * Gets the option parser for this shell and populates it with the command information for this shell
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand(
			'start',
			[
			    'help' => __('Starts and runs both the websocket service and the flashpolicy.')
			]
		)->description(__('Ratchet Websocket service.'))->addOption(
            'verbose',
            [
                'help' => 'Enable verbose output.',
                'short' => 'v',
                'boolean' => true
            ]
        );

		return $parser;
	}

}
