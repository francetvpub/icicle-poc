<?php
/**
 * Metaproxy
 * IndexController.php
 *
 * @copyright   Copyright (c) France Télévisions Publicité
 * @licence     Private, content should not be distributed
 */

namespace Application\Controller;

use Icicle\Coroutine\Coroutine;
use Icicle\Awaitable;
use Icicle\Coroutine\Exception\TerminatedException;
use Icicle\Loop;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function generator($time)
    {
        try {
            // Sets $start to the value returned by microtime() after approx. 1 second.
            $start = (yield Awaitable\resolve(microtime(true))->delay($time));

            echo "Sleep time: ", microtime(true) - $start, "\n";

            // Throws the exception from the rejected promise into the coroutine.
            yield Awaitable\reject(new \Exception('Rejected promise'));
        } catch (\Exception $e) { // Catches promise rejection reason.
            echo "Caught exception: ", $e->getMessage(), PHP_EOL;
        }

        yield Awaitable\resolve('Coroutine completed');
    }
    
    public function pocParallelAction()
    {
        $start = microtime(true);
        $wrapper1 = new Coroutine($this->generator(10));
        $wrapper1->timeout(4, function () use ($wrapper1) {
            echo 'wrapper1 timed out', PHP_EOL;
            $wrapper1->cancel();
        });
        $wrapper1->done(function ($data) {
            echo $data, PHP_EOL;
        });
        $wrapper2 = new Coroutine($this->generator(3));
        $wrapper2->timeout(4, function () use ($wrapper2) {
            echo 'wrapper2 timed out', PHP_EOL;
            $wrapper2->cancel();
        });
        $wrapper2->done(function ($data) {
            echo $data, PHP_EOL;
        });

        try {
            Loop\run();
        } catch (TerminatedException $e) {
            echo $e->getMessage(), PHP_EOL;
        }
        echo 'finished in: ', microtime(true) - $start, PHP_EOL;
    }
}
