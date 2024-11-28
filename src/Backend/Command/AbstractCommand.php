<?php

namespace App\Backend\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Aliases\Aliases;

class AbstractCommand extends Command
{
    protected $lockFile = "@runtime/lockfile.lock";

    public function __construct(
        protected Aliases $aliases
    ) {
        parent::__construct();
    }





    protected function isLocked()
    {
        return file_exists($this->aliases->get($this->lockFile));
    }

    protected function lock()
    {
        file_put_contents($this->aliases->get($this->lockFile), posix_getpid());
    }

    protected function unlock()
    {
        if (file_exists($this->aliases->get($this->lockFile))) {
            unlink($this->aliases->get($this->lockFile));
        }
    }

    protected function getLockPid()
    {
        $lockFile = $this->aliases->get($this->lockFile);
        $pid = null;

        if (file_exists($lockFile)) {
            $pid = (int)file_get_contents($lockFile);
        }

        return $pid;
    }

    protected function isPidRunning(?int $pid)
    {
        $isRunning = false;

        if (!is_null($pid)) {
            $isRunning = file_exists("/proc/{$pid}");
        }

        return $isRunning;
    }

    protected function unlockIfProcessIsNotRunning()
    {
        $lockPid = $this->getLockPid();
        $isRunning = $this->isPidRunning($lockPid);

        if (!$isRunning) {
            $this->unlock();
        }
    }
}
