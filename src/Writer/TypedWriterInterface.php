<?php

declare(strict_types=1);

namespace App\Writer;

interface TypedWriterInterface extends WriterInterface
{
    /**
     * There can be several mime types for a given format, this method should
     * return the most appopriate / popular one.
     *
     * @return string the mime type of the output
     */
    public function getDefaultMimeType(): string;

    /**
     * Returns a string best describing the format of the output (the file
     * extension is fine, for example).
     *
     * @return string a string without spaces, usable in a translation string
     */
    public function getFormat(): string;
}
