<?php

namespace PackageDealer\Console\Helper;

use Symfony\Component\Console\Helper\Helper,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Composer\IO\ConsoleIO as BaseIO;

class ConsoleIO extends Helper
{
    private $_io = null;
    
    public function __construct(BaseIO $io, $helper)
    {
        $this->setHelperSet($helper);
        $this->_io = $io;
    }
    
    public function getIO()
    {
        return $this->_io;
    }
    
    public function getName()
    {
        return 'io';
    }
    
    public function format($str, $style, $replaces=array())
    {
        return sprintf(
            '<%1$s>%2$s</%1$s>',
            $style,
            $str
        );
    }
    
    public function info($message)
    {
        $this->_io->write(
            $this->format($message, 'info')
        );
    }
    
    public function comment($message)
    {
        $this->_io->write(
            $this->format($message, 'comment')
        );
    }
    
    public function error($message)
    {
        $this->_io->write(
            $this->format($message, 'error')
        );
    }
    
    public function ask($question, $answers=array())
    {
        if (!empty($answers)) {
            $question .= $this->format(sprintf(
                '[%s] ',
                implode(',', $answers)
            ), 'comment');
        }
        return $this->_io->ask(
            $this->format(' - ' . $question, 'question')
        );
    }
    
    public function askRequired($question, $answers=array(), $maxRepeat=5, $validator=null)
    {
        $repeat = 0;
        $answer = null;
        do {
            $repeat++;
            $answer = trim($this->ask($question, $answers));
            if (empty($answer)) {
                $this->error(' Value is required and cannot be empty! ');
            } elseif (is_callable($validator)) {
                $result = call_user_func($validator, $answer);
                if (is_string($result)) {
                    $this->error($result);
                    $answer = null;
                }
            } else {
                for ($i=strlen($answer)-1; $i>=0; $i--) {
                    if (ctype_cntrl($answer[$i])) {
                        $this->error(' No control characters allowed! ');
                        $answer = null;
                    }
                }
            }
        } while(empty($answer) && $repeat<$maxRepeat);
        
        if ($repeat===$maxRepeat) {
            throw new \RuntimeException('Too many bad answers.');
        }
        return $answer;
    }
}