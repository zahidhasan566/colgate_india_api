<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\RSA;
use Exception;

class SFTPServiceBAc
{
    protected $sftp;
    protected $hostname;
    protected $username;
    protected $password;
    protected $privateKey;
    protected $port;
    public $localPath;
    protected $remoteFolder = '/aci-systems/colgate-data/';

    public function __construct()
    {
        // SFTP credentials
        $config = config('sftp');
        $this->hostname = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->port  = $config['port'];
        // Local path for storing the file temporarily
        $this->localPath = public_path('assets/colgateUploadedFile/');
    }

    /**
     * Connect to the SFTP server
     */
    public function connect()
    {
        try {
// Create a new SSH2 connection
            $ssh = new SSH2($this->hostname, $this->port, 30);

            // Disable host key verification
            $ssh->setPreferredAlgorithms([
                'hostkeys' => [] // Exclude host key algorithms
            ]);

            // Log the connection attempt
            Log::info('Attempting to connect to SFTP server...', [
                'hostname' => $this->hostname,
                'port' => $this->port
            ]);

            // Try login with password
            if (!$ssh->login($this->username, $this->password)) {
                throw new Exception('Login failed using password authentication.');
            }

            // Create an SFTP instance using the SSH2 connection
            $this->sftp = new SFTP($ssh);

            Log::info('SFTP connection successful.');
            return true; // Return true if login is successful
        } catch (Exception $e) {
            Log::error('SFTP connection error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]);
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

            // Check if the local file exists
            if (!file_exists($localFilePath)) {
                throw new Exception("Local file does not exist: $localFilePath");
            }

            // Construct the full remote file path using the static remote folder
//            $remoteFilePath = rtrim($this->remoteFolder, '/') . '/' . $remoteFileName;
            $this->sftp->clearStatCache();
            // Check if the remote directory exists
            if (!$this->sftp->file_exists($this->remoteFolder)) {
                throw new Exception("Remote directory does not exist: $this->remoteFolder. Please ensure the folder exists on the server.");
            }

            // Enable SFTP debugging
            define('NET_SFTP_LOGGING', SFTP::LOG_COMPLEX);

            // Upload the file to the remote path
            if (!$this->sftp->put($this->remoteFolder, $localFilePath, SFTP::SOURCE_LOCAL_FILE)) {
                // Log SFTP errors
                $sftpLogs = $this->sftp->getSFTPLog();
                Log::error('SFTP Logs:', ['logs' => $sftpLogs]);

                throw new Exception('File upload failed: ' . $this->sftp->getLastError());
            }

            return "File uploaded successfully to $this->remoteFolder!";
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
