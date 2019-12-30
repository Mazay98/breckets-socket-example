<?php
    require_once "vendor/autoload.php";

    $shortopts = "p:";
    $longopts = array(
        "port:"
    );
    $options = getopt($shortopts, $longopts);

    if (empty($options)) {
        die("need get port");
    }
    $port = current($options);
    $address = '0.0.0.0';

    $par = new Parentheses();

    if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
        echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error());
    }

    if (socket_bind($sock, $address, $port) === false) {
        echo "Не удалось выполнить socket_bind(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
    }

    if (socket_listen($sock, 5) === false) {
        echo "Не удалось выполнить socket_listen(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
    }

    do {
        if (($msgsock = socket_accept($sock)) === false) {
            echo "Не удалось выполнить socket_accept(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
            break;
        }
        /* Отправляем инструкции. */
        $msg = "\nДобро пожаловать на сервис по проверки скобок просто введите ваше выражение. \n" .
            "Чтобы отключиться, наберите 'exit'.\n";
        socket_write($msgsock, $msg, strlen($msg));

        do {
            if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                echo "Не удалось выполнить socket_read(): причина: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                break 2;
            }
            if (!$buf = trim($buf)) {
                continue;
            }
            if ($buf == 'exit') {
                break;
            }
			
			$array = [
                '(',
                ')',
                ' ',
                '\n',
                '\t',
                '\r',
            ];

            $data = str_replace($array,'',trim($buf));
			
			$talkback = "Log: Ваше выражение '$buf'.\n";
            if (!empty($data)) {
                $talkback.= "Log: " . "Не действительно!" . "\n";
            } else {
				$talkback.= "Log: " . (string)$par->isValid($buf) . "\n";
			}
            	

            socket_write($msgsock, $talkback, strlen($talkback));

        } while (true);
        socket_close($msgsock);
    } while (true);

    socket_close($sock);