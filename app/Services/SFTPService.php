<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\RSA;

class SFTPService
{
    protected $sftp;
    protected $hostname;
    protected $username;
    protected $password;
    protected $privateKey;
    protected $port;
    public $localPath;
    public $remoteFolder;

    public function __construct()
    {
        // SFTP credentials
        $config = config('sftp');
        $this->hostname = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->port  = $config['port'];
        $this->remoteFolder  = $config['remote_path'];
        // Local path for storing the file temporarily
        $this->localPath = public_path('assets/colgateUploadedFile/');
    }

    /**
     * Connect to the SFTP server
     */
    public function connect()
    {
        try {
            // Create a new SFTP instance with a custom port and a timeout of 30 seconds
            $this->sftp = new SFTP($this->hostname, $this->port, 30); // Port is set to 8854

            // If using private key authentication
            if (!$this->sftp->login($this->username, $this->password)) {
                throw new Exception('Login failed using password authentication.');
            }

            return true; // Return true if login is successful
        } catch (Exception $e) {
            // Log the exception error with file, line, and message
            Log::error('SFTP connection error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]);

            // Optionally, rethrow the exception if you want to handle it elsewhere
            throw $e;
        }
    }

    /**
     * Upload a file to the SFTP server
     *
     * @param string $localPath The local file path to upload
     * @param string $remotePath The remote path on the SFTP server
     * @return string
     */
    public function uploadFile($localFilePath, $remoteFileName)
    {
        try {
            // Ensure the connection is made before attempting the upload
            $this->connect();
            $remoteFilePath = rtrim($this->remoteFolder, '/') . '/' . $remoteFileName;
            // Upload the file to the remote path
            if (!$this->sftp->put($remoteFilePath, $localFilePath, SFTP::SOURCE_LOCAL_FILE)) {
                throw new Exception('File upload failed: ' . $this->sftp->getLastError());
            }

            return "File uploaded successfully!";
        } catch (Exception $e) {
            // Log the exception error with file, line, and message
            Log::error('SFTP upload error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]);

            // Optionally, rethrow the exception if you want to handle it elsewhere
            throw $e;
        }
    }
}



