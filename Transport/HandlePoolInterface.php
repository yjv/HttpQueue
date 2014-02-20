<?php
namespace Yjv\HttpQueue\Transport;

use Yjv\HttpQueue\Transport\HandleInterface;

interface HandlePoolInterface
{
    /**
     * queue a configured handle for execution
     *
     * @param HandleInterface $handle
     * @return self
     */
    public function queueHandle(HandleInterface $handle);

    /**
     * unqueue handle before it executes
     *
     * @param HandleInterface $handle
     * @return self
     */
    public function unqueueHandle(HandleInterface $handle);

    /**
     * create a new handle or recycle an already used on
     * either way the handle should be in the same unconfigured state
     *
     * @param array $options
     * @return self
     */
    public function createHandle(array $options = array());

    /**
     * @return bool if the execution was successful
     */
    public function execute();

    /**
     * get the response content for an executed handle
     *
     * @param HandleInterface $handle
     * @return string
     */
    public function getHandleResponseContent(HandleInterface $handle);

    /**
     *
     * wait until some handles are finished transfer
     * @param float $timeout
     * @return bool if handles are finished
     */
    public function select($timeout = 1.0);

    /**
     * get an array of handles that have finished transfer
     *
     * @return HandleInterface[]
     */
    public function getFinishedHandles();

    /**
     * close the handle pool
     *
     * @return self
     */
    public function close();

    /**
     * return the underlying php resource
     *
     * @return reosurce
     */
    public function getResource();

    /**
     * get the amount of handles that are currently executing
     *
     * @return int
     */
    public function getExecutingCount();
}
