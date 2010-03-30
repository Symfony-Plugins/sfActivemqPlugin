<?php
class ActivemqConnection {
	function __construct() {
        $config = sfYaml::load(sfConfig::get('sf_root_dir')."/plugins/sfActivemqPlugin/config/config.yml");
        $this->host = $config['activemq_server']['host'];
        $this->port = $config['activemq_server']['port'];
        $this->protocol = $config['activemq_server']['protocol'];
	}
	
    function connect($queue_name=null) {
        $this->connection = new Stomp($this->protocol.'://'.$this->host.':'.$this->port);
        $this->connection->connect();
        $queue_name && $this->subscribe($queue_name);
        if($this->connection == false) {
            $this->disconnect();
        }
    }
    
    function subscribe($queue_name) {
    	$this->current_queue = "/queue/$queue_name";
    	$this->connection->subscribe($this->current_queue);
    }
    
    function disconnect() {
        $this->connection->disconnect();     
    }
    
    function readMessage($ack=true) {
        if($queue_item = $this->connection->readFrame()) {
            $this->last_message = unserialize($queue_item->body);
            $ack && $this->connection->ack($this->last_message);
            return $this->last_message;
        }
        return false;
    }
    
    function send($message) {
        $this->connection->send($this->current_queue, serialize($message), array("persistent"=>"true"));
    }
} 
