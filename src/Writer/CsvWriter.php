<?php

declare(strict_types=1);

namespace App\Writer;

class CsvWriter implements TypedWriterInterface
{
    /**
     * @throws \RuntimeException
     */
    public function __construct(
        private string $filename,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '\\',
        private bool $showHeaders = false,
        private bool $withBom = false,
        private string $terminate = "\n",
        protected int $importCount = 0,
        private int $position = 0
    ) {

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
        $this->importCount=$this->position;
    }

    private function addHeaders(array $data): void
    {
        $headers = [];
        foreach ($data as $header => $value) {
            $headers[] = $header;
        }

        fputcsv($this->file, $headers, $this->delimiter, $this->enclosure, $this->escape);
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
