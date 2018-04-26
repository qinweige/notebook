<?php

class user implements \SplSubject
{
    private $email;
    private $observers;

    public function __construct()
    {
        $this->observers = new \SplObjectStorage();
    }
    public function attach(SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    public function detach(SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    public function changeEmail($email)
    {
        $this->email = $email;
        $this->notify();
    }
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}

include "../designPattern/listener.php";
$oneObserver = new oneObserver();
$oneObserver->getDetail();
$user = new user();
$user->attach($oneObserver);
$user->changeEmail('hello');
$oneObserver->getDetail();

