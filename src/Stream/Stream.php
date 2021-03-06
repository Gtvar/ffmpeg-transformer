<?php

namespace Javer\FfmpegTransformer\Stream;

use Javer\FfmpegTransformer\File\FileInterface;

/**
 * Class Stream
 *
 * @package Javer\FfmpegTransformer\Stream
 */
abstract class Stream implements StreamInterface
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var string|integer|null
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $isInput;

    /**
     * @var string[]
     */
    protected $options = [];

    /**
     * @var boolean
     */
    protected $isCustomCodec = false;

    /**
     * @var boolean
     */
    protected $isMapped = false;

    /**
     * Stream constructor.
     *
     * @param FileInterface $file
     * @param string|null   $name
     * @param string        $type
     * @param boolean       $isInput
     * @param boolean       $isMapped
     */
    public function __construct(FileInterface $file, $name = null, $type = '', $isInput = false, $isMapped = true)
    {
        $this->file = $file;
        $this->name = is_integer($name) ? trim(implode(':', [$this->file->getName(), $type, $name]), ':') : $name;
        $this->type = $type;
        $this->isInput = $isInput;
        $this->isMapped = $isMapped;
    }

    /**
     * Build command.
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function build(): array
    {
        if (!$this->isInput && !$this->isMapped) {
            throw new \LogicException(sprintf('Stream "%s" is not connected to any output', $this->getName()));
        }

        $options = $this->options;

        if (!$this->isInput && !$this->isCustomCodec) {
            $options[] = sprintf('-c:%s', $this->getName());
            $options[] = 'copy';
        }

        return $options;
    }

    /**
     * Returns a string representation of the stream.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(' ', array_map('escapeshellarg', $this->build()));
    }

    /**
     * Clones the current stream.
     */
    public function __clone()
    {
        $this->options = [];
    }

    /**
     * Returns stream name.
     *
     * @return string
     */
    public function getName(): string
    {
        if (is_null($this->name)) {
            $streamNumber = $this->file->getStreamNumber($this);

            $this->name = trim(implode(':', [$this->file->getName(), $this->getType(), $streamNumber]), ':');
        }

        return $this->name;
    }

    /**
     * Returns stream type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns whether stream is input.
     *
     * @return boolean
     */
    public function getInput(): bool
    {
        return $this->isInput;
    }

    /**
     * Move stream to the given position (stream index) in the output file.
     *
     * @param integer $position
     *
     * @return StreamInterface
     *
     * @throws \LogicException
     */
    public function moveTo(int $position)
    {
        $this->file->moveStreamToPosition($this, $position);

        return $this;
    }

    /**
     * Return to file.
     *
     * @return FileInterface
     */
    public function end(): FileInterface
    {
        return $this->file;
    }
}
