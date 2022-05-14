<?php

declare(strict_types=1);

namespace App\Writer;

class JsonWriter implements TypedWriterInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var resource|null
     */
    private $file;

    /**
     * @var int
     */
    private $position = 0;
    /**
    * @var int
    */
   protected $importCount = 0;

    /**
     * @throws \RuntimeException
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;

        if (is_file($filename)) {
            throw new \RuntimeException(sprintf('The file %s already exist', $filename));
        }
    }

    public function getDefaultMimeType(): string
    {
        return 'application/json';
    }

    public function getFormat(): string
    {
        return 'json';
    }

    public function open(): void
    {
        $this->file = fopen($this->filename, 'w', false);

        fwrite($this->file, '[');
    }

    public function close(): void
    {
        fwrite($this->file, ']');

        fclose($this->file);
    }

    public function write(array $data): void
    {
        fwrite($this->file, ($this->position > 0 ? ',' : '').json_encode($data));

        ++$this->position;
        $this->importCount=$this->position;
    }
    
    /**
     * Get import count.
     *
     * @return int
     */
    public function getImportCount(): int
    {
        return $this->importCount;
    }
}