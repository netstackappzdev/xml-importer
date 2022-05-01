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
use App\Lib\FTPClient;
use Symfony\Component\Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class ReadXML {
    public xmlFileReader $XmlFileReader;
    private $filename;    
    private $logger;
    protected $projectDir;
    /** @var GoogleSheetsImport */
    private GoogleSheetsImport $sheets;

    // public function __construct(LoggerInterface $logger)
    // {
    //     $this->logger = $logger;
    //     $this->sheets = new GoogleSheetsImport('YourApp', '/config/google-api.json');
    // }

    public function convert($fetch,$to){
        $this->projectDir = dirname(__FILE__, 3);
        $dotenv = new Dotenv();
        $env = $dotenv->load($this->projectDir .'/.env');
        //echo getenv('APP_ENV');
        //echo 'My username is ' .$_ENV["SERVER_PASSWORD"] . '!';
        // create a log channel
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler('log/import-xml.log'));

        //echo $env; die;
        $result = '';

        if($result = $this->load($fetch)){
            $result .= $this->saveCSV();
            if($to=='JSON') $result .= $this->saveJson();
            if($to=='GoogleSheet') $result .= $this->importGoogleSheet();
        }
        return $result;

    }

    public function load($fetch){        
        $this->logger->info("starting to load XML file from $fetch");   
        if($fetch=='server'){
            $this->connectServer();
        }
        $file = new FileLocator($this->projectDir . '/public/');
        $this->filename = $_ENV['SERVER_XML_SAVE_FILENAME'];
        $this->xmlFileReader = new XmlFileReader($file);
        if($this->xmlFileReader->supports($this->filename)){
            $data= $this->xmlFileReader->load($this->filename);
            $this->xmlData=$data['row'];
        } else {
            $this->logger->error('file format is wrong');
            return true;
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
            $file = $this->projectDir. '/public/sample-'.date('m-d-Y_H:i:s').'.csv';
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
            $file = $this->projectDir. '/public/sample-'.date('m-d-Y_H:i:s').'.json';
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
        $authConfig = $this->projectDir.$_ENV['GHSEET_AUTH_CONFIG'];
        $tokenPath = $this->projectDir.$_ENV['GSHEET_TOKEN_JSON'];
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
        $ftpUsername = $_ENV['SERVER_USERNAME'];
        $ftpPassword = $_ENV['SERVER_PASSWORD']; 
        $ftpHost = $_ENV['SERVER_HOST'];
        $ftpPassiveMode = $_ENV['SERVER_FTP_PASSIVE_MODE'];
        $fileFrom = $_ENV['SERVER_XML_FILE_PATH'];
        $fileTo = $_ENV['SERVER_XML_SAVE_DIR'].'/'.$_ENV['SERVER_XML_SAVE_FILENAME'];

        $ftpClient = new FTPClient($this->logger);
        $ftpClient->connect($ftpHost,$ftpUsername,$ftpPassword,true);
        $listfiles = $ftpClient->downloadFile($fileFrom,$this->projectDir.$fileTo);         
    }

}
