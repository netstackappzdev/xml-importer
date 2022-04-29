<?php
namespace App\Writer;

class CsvWriter implements TypedWriterInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $escape;

    /**
     * @var resource|null
     */
    private $file;

    /**
     * @var bool
     */
    private $showHeaders;

    /**
     * @var int
     */
    private $position;

    /**
     * @var bool
     */
    private $withBom;

    /**
     * @var string
     */
    private $terminate;

    /**
     * @throws \RuntimeException
     */
    public function __construct(
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\',
        bool $showHeaders = true,
        bool $withBom = false,
        string $terminate = "\n"
    ) {
        $this->filename = $filename;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->showHeaders = $showHeaders;
        $this->terminate = $terminate;
        $this->position = 0;
        $this->withBom = $withBom;

        if (is_file($filename)) {
            throw new \RuntimeException(sprintf('The file %s already exist', $filename));
        }
    }

    public function getDefaultMimeType(): string
    {
        return 'text/csv';
    }

    public function getFormat(): string
    {
        return 'csv';
    }

    public function open(): void
    {
        $this->file = fopen($this->filename, 'w', false);
        if ("\n" !== $this->terminate) {
            stream_filter_register('filterTerminate', CsvWriterTerminate::class);
            stream_filter_append($this->file, 'filterTerminate', \STREAM_FILTER_WRITE, ['terminate' => $this->terminate]);
        }
        if (true === $this->withBom) {
            fprintf($this->file, \chr(0xEF).\chr(0xBB).\chr(0xBF));
        }
    }

    public function close(): void
    {
        fclose($this->file);
    }

    public function write(array $data): void
    {
        if (0 === $this->position && $this->showHeaders) {
            $this->addHeaders($data);

            ++$this->position;
        }

        $result = @fputcsv($this->file, $data, $this->delimiter, $this->enclosure, $this->escape);

        ++$this->position;
    }

    private function addHeaders(array $data): void
    {
        $headers = [];
        foreach ($data as $header => $value) {
            $headers[] = $header;
        }

        fputcsv($this->file, $headers, $this->delimiter, $this->enclosure, $this->escape);
    }
}
