<?php

class WebSocketServer
{
    private $address;
    private $port;
    private $sock;

    public function __construct($address = '127.0.0.1', $port = 12345)
    {
        $this->address = $address;
        $this->port = $port;
        $this->init();
    }

    private function init()
    {
        // Creamos un socket
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->sock === false) {
            throw new Exception("socket_create() falló: razón: " . socket_strerror(socket_last_error()));
        }

        // Asignamos la dirección y el puerto al socket
        if (socket_bind($this->sock, $this->address, $this->port) === false) {
            throw new Exception("socket_bind() falló: razón: " . socket_strerror(socket_last_error($this->sock)));
        }

        // Ponemos el socket en modo de escucha
        if (socket_listen($this->sock, 5) === false) {
            throw new Exception("socket_listen() falló: razón: " . socket_strerror(socket_last_error($this->sock)));
        }

        echo "Servidor WebSocket iniciado en {$this->address}:{$this->port}\n";
    }

    public function run()
    {
        do {
            // Aceptamos conexiones entrantes
            if (($msgsock = socket_accept($this->sock)) === false) {
                throw new Exception("socket_accept() falló: razón: " . socket_strerror(socket_last_error($this->sock)));
            }

            // Mensaje de conexión exitosa
            $msg = "Servidor WebSocket: ¡Conexión exitosa!\n";
            socket_write($msgsock, $msg, strlen($msg));

            while (true) {
                // Leemos el mensaje recibido del cliente
                $buf = socket_read($msgsock, 2048, PHP_NORMAL_READ);
                if ($buf === false) {
                    throw new Exception("socket_read() falló: razón: " . socket_strerror(socket_last_error($msgsock)));
                }
                if (!$buf = trim($buf)) {
                    continue;
                }
                if ($buf == 'exit') {
                    break;
                }
                // Enviamos una respuesta al cliente
                $talkback = "Cliente: $buf\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                echo "$buf\n";
            }
            // Cerramos la conexión con el cliente
            socket_close($msgsock);
        } while (true);
    }

    public function __destruct()
    {
        // Cerramos el socket principal
        socket_close($this->sock);
    }
}

try {
    $server = new WebSocketServer();
    $server->run();
} catch (Exception $e) {
    echo "Error al iniciar el servidor: " . $e->getMessage() . "\n";
}
?>
