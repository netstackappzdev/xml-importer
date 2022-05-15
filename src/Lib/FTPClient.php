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
    
    public function __deconstruct()
    {
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

    public function connect($server, $ftpUser, $ftpPassword, $isPassive = false)
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

    public function downloadFile($fileFrom, $fileTo)
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