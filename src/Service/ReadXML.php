<?php

namespace App\Service;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Config\FileLocator;
use App\Loader\XmlFileReader;
use League\Csv\Writer;
use App\Lib\GoogleSheetsImport;
use Psr\Log\LoggerInterface;

use App\Writer\JsonWriter;
use App\Writer\CsvWriter;

class ReadXML {
    public xmlFileReader $XmlFileReader;
    private $filename;    
    private $logger;


    /** @var GoogleSheetsImport */
    private GoogleSheetsImport $sheets;

    // public function __construct(LoggerInterface $logger)
    // {
    //     $this->logger = $logger;
    // }

    /**
     * importManagementPropertyInfoCommand constructor.
     * @throws Google_Exception
     */
    // public function __construct()
    // {
    //     $this->sheets = new GoogleSheetsImport('YourApp', '/config/google-api.json');
    // }

    public function convert($fetch,$to){
        $result = '';
        if($result = $this->load($fetch)){
            $result .= $this->saveCSV();
            if($to=='JSON') $result .= $this->saveJson();
            if($to=='GoogleSheet') $result .= $this->importGoogleSheet();
        }
        return $result;

    }

    public function load($fetch){
        if($fetch=='local'){
            $file = new FileLocator(dirname(__FILE__, 3) . '/public/');
            $this->filename = 'employee.xml';
            //$file = dirname(__FILE__, 3). '/public/employee.xml'; 
            $this->xmlFileReader = new XmlFileReader($file);
            if($this->xmlFileReader->supports($this->filename)){
                $data= $this->xmlFileReader->load($this->filename);
                $this->xmlData=$data['row'];
            } else {
                echo "file format is wrong";
                return true;
            }   
        }elseif($fetch=='server'){
            //$this->connectServer();
        }

        return true;  
    }

    public function saveCSV(){
        // fetch the keys of the first json object
        $data = $this->xmlData;
        $headers = array_keys(current($data));

        // flatten the json objects to keep only the values as arrays
        $formattedData = [];
        foreach ($data as $jsonObject) {
            $jsonObject = array_map('strval', $jsonObject);
            $formattedData[] = array_values($jsonObject);
        }

        $sheetTittle = array();
        $sheetTittle[] = $headers;
        $outputArray = array_merge($sheetTittle, $formattedData);
        $this->xmlData['header']=$headers;
        $this->xmlData['data']=$formattedData;

        try {
            $file = dirname(__FILE__, 3). '/public/sample-'.date('m-d-Y_H:i:s').'.csv';
            // insert the headers and the rows in the CSV file
            // $csv = Writer::createFromPath($file, 'w');
            // $csv->insertOne($headers);
            // $csv->insertAll($formattedData);

            $writer = new CsvWriter($file, ',', '"', '\\', false);
            $writer->open();
            foreach($outputArray as $outputdata){
                $writer->write($outputdata);
            }

            $writer->close();

        }
        catch(IOException $e) {
            //log code here
        }
    }

    private function saveJson(){
        try {
            $file = dirname(__FILE__, 3). '/public/sample-'.date('m-d-Y_H:i:s').'.json';
            $writer = new JsonWriter($file);
            $writer->open();
            $writer->write($this->xmlData['data']);
            $writer->close();
        }
        catch(IOException $e) {
            //log code here
        }
    }

    private function importGoogleSheet(){
        $authConfig = dirname(__FILE__, 3).'/config/google/client_secret_348365894735-1kb5idgb6dur3tmb90u0shlegrljo18j.apps.googleusercontent.com.json';
        $tokenPath = dirname(__FILE__, 3).'/config/google/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
        }
        $this->sheets = new GoogleSheetsImport('Google Sheets API PHP Quickstart', $authConfig,$accessToken );

        $sheetTittle = array();
        $sheetTittle[] = $this->xmlData['header'];
        $outputArray = array_merge($sheetTittle, $this->xmlData['data']);
        $this->sheets->importData('products up interview test',$outputArray);
    }

    private function connectServer(){
        // FTP server details
        
        $ftpHost   = '';
        $ftpUsername = '';
        $ftpPassword = '';

        // open an FTP connection
        $connId = ftp_connect($ftpHost) or die("Couldn't connect to $ftpHost");

        // login to FTP server
        $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);

        // local & server file path
        $localFilePath  = '/';
        $remoteFilePath = '';

        // try to download a file from server
        if(ftp_get($connId, $localFilePath, $remoteFilePath, FTP_BINARY)){
            echo "File transfer successful - $localFilePath";
        }else{
            echo "There was an error while downloading $localFilePath";
        }

        // close the connection
        ftp_close($connId);
    }

}
