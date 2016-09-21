#!/usr/bin/env php
<?php

use Icicle\Coroutine\Coroutine;
use Icicle\Awaitable;
use Icicle\Coroutine\Exception\TerminatedException;
use Icicle\Loop;

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

function generator($time)
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


$start = microtime(true);
$wrapper1 = new Coroutine(generator(10));
$wrapper1->timeout(4, function () use ($wrapper1) {
    echo 'wrapper1 timed out', PHP_EOL;
    $wrapper1->cancel();
});
$wrapper1->done(function ($data) {
    echo $data, PHP_EOL;
});
$wrapper2 = new Coroutine(generator(3));
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