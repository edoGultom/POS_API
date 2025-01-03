<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use OpenSwoole\WebSocket\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WebSocketController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {

        $server = new Server("127.0.0.1", 8085);

        $server->on("Start", function (Server $server) {
            echo "OpenSwoole WebSocket Server is started at http://127.0.0.1:8085\n";
        });

        $server->on('Open', function (Server $server, OpenSwoole\Http\Request $request) {
            echo "connection open: {$request->fd}\n";

            $server->tick(1000, function () use ($server, $request) {
                $server->push($request->fd, json_encode(["hello", time()]));
            });
        });

        $server->on('Message', function (Server $server, Frame $frame) {
            echo "received message: {$frame->data}\n";
            $server->push($frame->fd, json_encode(["hello", time()]));
        });

        $server->on('Close', function (Server $server, int $fd) {
            echo "connection close: {$fd}\n";
        });

        $server->on('Disconnect', function (Server $server, int $fd) {
            echo "connection disconnect: {$fd}\n";
        });

        $server->start();

        // $server = new WebSocket([
        //     'host' => '127.0.0.1',
        //     'port' => 9501,
        // ]);

        // $server->on('open', function (WebSocketEvent $event) {
        //     echo "Koneksi WebSocket dibuka\n";
        // });

        // $server->on('message', function (WebSocketEvent $event) {
        //     echo "Pesan diterima: " . $event->getMessage() . "\n";
        //     $event->send("Pesan diterima!");
        // });

        // $server->on('close', function (WebSocketEvent $event) {
        //     echo "Koneksi WebSocket ditutup\n";
        // });

        // $server->start();


    }
}
