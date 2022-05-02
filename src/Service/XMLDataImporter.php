<?php

namespace App\Service;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Config\FileLocator;
use League\Csv\Writer;
use App\Lib\GoogleSheetsImport;
use Psr\Log\LoggerInterface;

use App\Reader\XmlFileReader;
use App\Writer\JsonWriter;
use App\Writer\CsvWriter;
use App\Lib\FTPClient;
use Symfony\Component\Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Command\MyDependency;



class XMLDataImporter {
    public xmlFileReader $XmlFileReader;
    private $filename;    
    private $logger;
    public $consoleLogger;
    protected $projectDir;
    /** @var GoogleSheetsImport */
    private GoogleSheetsImport $sheets;

    // public function __construct(LoggerInterface $logger)
    // {
    //     $this->logger = $logger;
    //     $this->sheets = new GoogleSheetsImport('YourApp', '/config/google-api.json');
    // }

    /**
	 * convert XML file into CSV, JSON
	 *
	 * @param string $fetch Server or Local
	 *
	 * @return bool
	 */
    public function convert(string $fetch, string $to,MyDependency $consoleLogger) : bool
    {
        $this->projectDir       = dirname(__FILE__, 3);
        $dotenv                 = new Dotenv();
        $env                    = $dotenv->load($this->projectDir .'/.env');
        $this->consoleLogger    = $consoleLogger;
        $this->logger           = new Logger('name');
        $this->logger->pushHandler(new StreamHandler('log/import-xml.log'));

        $result = false;
        if($result = $this->load($fetch)){
            $this->saveCSV();
            if($to=='JSON') $this->saveJson();
            if($to=='GoogleSheet') $this->importGoogleSheet();
        }
        return $result;

    }

    /**
	 * load the XML file into Array and store the data in variable
	 *
	 * @param string $fetch 
	 *
	 * @return bool
	 */
    public function load(string $fetch){        
        $this->logging("starting to load XML file from $fetch",'info');

        if($fetch=='server'){
            $this->connectServer();
        }
        $file                   = new FileLocator($this->projectDir . '/public/');
        $this->filename         = $_ENV['SERVER_XML_SAVE_FILENAME'];
        $this->xmlFileReader    = new XmlFileReader($file);
        if($this->xmlFileReader->supports($this->filename)){
            $data= $this->xmlFileReader->load($this->filename);
            if(!$this->xmlFileReader->validFile){
                $this->logging("$data",'error');
                return false;
            }
            $this->xmlData=$data['book']; //$data['row'];
        } else {
            $this->logging("file format is wrong",'error');
            return false;
        }   

        return true;  
    }

    public function saveCSV(){
        // fetch the keys of the first json object
        $headers = array_keys(current($this->xmlData));

        // flatten the json objects to keep only the values as arrays
        $formattedData = [];
        foreach ($this->xmlData as $jsonObject) {
            $jsonObject = array_map('strval', $jsonObject);
            $formattedData[] = array_values($jsonObject);
        }

        $sheetTittle            = array();
        $sheetTittle[]          = $headers;
        $outputArray            = array_merge($sheetTittle, $formattedData);
        $this->xmlData['header']=$headers;
        $this->xmlData['data']  =$formattedData;

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
            $this->logging("converted XML files into CSV file and stored in $file",'info');
            $writer->close();

        }
        catch(IOException $e) {
            $this->logging("$e",'error');
        }
    }

    private function saveJson(){
        try {
            $file   = $this->projectDir. '/public/sample-'.date('m-d-Y_H:i:s').'.json';
            $writer = new JsonWriter($file);
            $writer->open();
            $writer->write($this->xmlData['data']);
            $writer->close();
            $this->logging("converted XML files into JSON file and stored  in $file",'info');
        }
        catch(IOException $e) {
            //log code here
            $this->logging("$e",'error');
        }
    }

    private function importGoogleSheet(){
        $authConfig = $this->projectDir.$_ENV['GHSEET_AUTH_CONFIG'];
        $tokenPath  = $this->projectDir.$_ENV['GSHEET_TOKEN_JSON'];
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
        }
        $this->sheets   = new GoogleSheetsImport('imported XML file', $authConfig,$accessToken );
        $sheetTittle    = array();
        $sheetTittle[]  = $this->xmlData['header'];
        $outputArray    = array_merge($sheetTittle, $this->xmlData['data']);
        $sheetId        = $this->sheets->importData('products up interview test',$outputArray);
        $this->logging("imported XML data into Google Sheet and created id is $sheetId",'info');
    }

    private function connectServer(){    
        $ftpUsername    = $_ENV['SERVER_USERNAME'];
        $ftpPassword    = $_ENV['SERVER_PASSWORD']; 
        $ftpHost        = $_ENV['SERVER_HOST'];
        $ftpPassiveMode = $_ENV['SERVER_FTP_PASSIVE_MODE'];
        $fileFrom       = $_ENV['SERVER_XML_FILE_PATH'];
        $fileTo         = $_ENV['SERVER_XML_SAVE_DIR'].'/'.$_ENV['SERVER_XML_SAVE_FILENAME'];

        $ftpClient = new FTPClient($this->logger);
        $ftpClient->connect($ftpHost,$ftpUsername,$ftpPassword,true);
        $listfiles = $ftpClient->downloadFile($fileFrom,$this->projectDir.$fileTo);         
    }

    private function logging($message,$type){
        $this->logger->$type($message);   
        $this->consoleLogger->$type($message);
    }

}
