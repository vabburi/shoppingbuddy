<?php include_once("index.html");
require('vendor/autoload.php');
define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
$url = parse_url(getenv('RABBITMQ_BIGWIG_TX_URL')) or exit('unable to read env variable to send ');
var_dump($url);

$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1)) or exit('unable to open AMQP Connection');
$ch = $conn->channel();

$exchange = 'amq.direct';
$queue = 'basic_get_queue';
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', true, true, false);
$ch->queue_bind($queue, $exchange);

$msg_body = 'the body';
$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
$ch->basic_publish($msg, $exchange) or exit('unable to publish message on rabbitmq');

$url = parse_url(getenv('RABBITMQ_BIGWIG_RX_URL')) or exit('unable to read env variable to receive ');
var_dump($url);

$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1)) or exit('unable to open AMQP Connection');
$ch = $conn->channel();

$exchange = 'amq.direct';
$queue = 'basic_get_queue';


$retrived_msg = $ch->basic_get($queue) or exit('unable to retrieve message from rabbitmq');;
echo "printing msg retrieved";
var_dump($retrived_msg->body);
$ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);

$ch->close();
$conn->close();