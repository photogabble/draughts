<?php

namespace Photogabble\Draughts;

/**
 * Class CaptureState
 * This is a basic data container object.
 */
class CaptureState
{
    /**
     * @var string
     */
    public $position;
    /**
     * @var string
     */
    public $dirFrom;

    /**
     * CaptureState constructor.
     *
     * @param string $position
     * @param string $dirFrom
     */
    public function __construct(string $position, string $dirFrom = '')
    {
        $this->position = $position;
        $this->dirFrom = $dirFrom;
    }

    /**
     * Ensure clone makes a new instance.
     *
     * @return CaptureState
     */
    public function __clone()
    {
        return new CaptureState($this->position, $this->dirFrom);
    }
}