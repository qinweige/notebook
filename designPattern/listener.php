<?php

class oneObserver implements \SplObserver 
{
    private $detail = [];

    public function update(\SplSubject $splSubject)
    {
        $this->detail[] = $splSubject;
    }

    public function getDetail()
    {
        print_r($this->detail);
        print_r('<br>');
    }
}