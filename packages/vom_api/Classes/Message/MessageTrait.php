<?php
namespace Vom\Vomapi\Message;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait MessageTrait {

    private array $message = [
        'success' => [],
        'error' => []
    ];

    private $messenger;

    public function setMessenger(SymfonyStyle $messenger)
    {
        $this->messenger = $messenger;
    }

    public function setSuccess(string $message)
    {
        $this->message['success'][] = $message;
        $this->messenger->success($message);
    }

    public function getSuccess()
    {
        return $this->message['success'];
    }

    public function setError(string $message)
    {
        $this->message['error'][] = $message;
        $this->messenger->error($message);
    }

    public function getError()
    {
        return $this->message['error'];
    }


}