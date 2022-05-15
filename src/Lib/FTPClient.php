<?php

declare(strict_types=1);

namespace App\Lib;

class FTPClient
{
    public function __construct(
        public $logger,
        private $connectionId = '',
        private bool $loginOk = false
    )
    {}

    /**
     * Close the connection when the object is destroyed.
     */
    public function __deconstruct()
    {
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

    /**
     * Open a FTP connection.
     * @param string $server 
     * @param string $ftpUser  
     * @param string $ftpPassword  
     * @return bool 
     */
    public function connect($server, $ftpUser, $ftpPassword, $isPassive = false): bool
    {
        $this->connectionId = ftp_connect($server);
        $loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);
        ftp_pasv($this->connectionId, $isPassive);
        if (!$this->connectionId || !$loginResult) {
            $this->logger->error('FTP connection has failed!');
            $this->logger->error('Attempted to connect to ' . $server . ' for user ' . $ftpUser);
            return false;
        } else {
            $this->logger->info('Connected to ' . $server . ', for user ' . $ftpUser);
            $this->loginOk = true;
            return true;
        }
    }

    /**
     * Starts downloading a remote file.
     * @param string $fileFrom The remote file to download.
     * @param string $fileTo  The local file path.
     * @return bool 
     */
    public function downloadFile($fileFrom, $fileTo): bool
    {
        $asciiArray = array('txt', 'csv');
        $fileParts = explode('.', $fileFrom);
        $extension = end($fileParts);
        if (in_array($extension, $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        if (ftp_get($this->connectionId, $fileTo, $fileFrom, $mode, 0)) {
            $this->logger->info(' file "' . $fileTo . '" successfully downloaded');
            return true;
        } else {
            $this->logger->error('There was an error downloading file "' . $fileFrom . '" to "' . $fileTo . '"');
            return false;
        }
    }
}