Configuration
=============

There are as many different configuration settings as there are facets to Ratchet.

# Connections #

There are a few different connection settings groups involved.

## Websocket ##

The websocket group contains 2 settings `ip` and `port`.

```php
Configure::write('Ratchet.Connection.websocket', [
    'address' => '0.0.0.0', // The IP to listen on
    'port' => 11001, // The port to listen on
]);
```

## External ##

The external group defines the external interface of the websocket server.

```php
Configure::write('Ratchet.Connection.external', [
    'hostname' => 'cakeplugindev.xps8500dev', // The hostname (or IP) serving the websocket server
    'port' => 80, // The port the websocket server is served over (note that this can be on the same ort as your HTTP server due to proxies like HAproxy or nginx)
    'path' => 'websocket', // Path
    'secure' => false, // Secure connection or not
]);
```

Note for [HAproxy check here](http://socketo.me/docs/deploy#serverconfiguration) and [nginx here](http://blog.wyrihaximus.net/2013/05/serving-websockets-and-cakephp-on-the-same-domain-and-port-with-nginx/).

## Keep alive ##

The internet is a rough place and connections sometimes terminate due to inactivity. Ratchet utilizes a keep alive mechanism broadcasting a ping to all connected clients. This setting is in seconds.

```php
Configure::write('Ratchet.Connection.keepaliveInterval', 23);
```

# Client #

On the client side there are 2 settings. The retry delay (in miliseconds) and the number of times to try reconnecting to the server.

```php
Configure::write('Ratchet.Client', [
	'retryDelay' => 500, // Not the best option but it speeds up development
	'maxRetries' => 500, // Keep on trying! (Also not the best option)
]);
```
